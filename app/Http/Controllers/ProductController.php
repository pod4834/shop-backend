<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function index() { return Product::all(); }

    public function store(Request $request)
    {
        $imageUrl = '';
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $imageUrl = asset('storage/' . $path);
        }

        $product = new Product();
        $product->category = $request->category;
        $product->product_id = $request->product_id;
        $product->product_name = $request->product_name; // 새 이름 적용
        $product->product_detail = $request->product_detail; // 새 이름 적용
        $product->detail_url = $request->detail_url;
        $product->price = $request->price;
        $product->cv = $request->cv;
        $product->pv = $request->pv;
        $product->img_url = $imageUrl; 
        $product->save();

        return response()->json(['message' => '상품이 성공적으로 등록되었습니다!']);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->category = $request->category;
        $product->product_id = $request->product_id;
        $product->product_name = $request->product_name;
        $product->product_detail = $request->product_detail;
        $product->detail_url = $request->detail_url;
        $product->price = $request->price;
        $product->cv = $request->cv;
        $product->pv = $request->pv;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $product->img_url = asset('storage/' . $path);
        }
        $product->save();

        return response()->json(['message' => '상품 정보가 수정되었습니다!']);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => '상품이 삭제되었습니다.']);
    }

    // 💡 엑셀 일괄 등록 - 9개의 데이터를 읽도록 변경
    public function bulkStore(Request $request)
    {
        if (!$request->hasFile('csv_file')) {
            return response()->json(['message' => '파일이 없습니다.'], 400);
        }

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), "r");

        fgetcsv($handle); // 1번째 줄(헤더) 건너뛰기

        $count = 0;
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // CSV 순서: 0:category, 1:product_id, 2:product_name, 3:product_detail, 4:detail_url, 5:price, 6:cv, 7:pv, 8:img_url
            if (count($data) >= 9) {
                $product = new Product();
                $product->category = $data[0];
                $product->product_id = $data[1];
                $product->product_name = $data[2];
                $product->product_detail = $data[3];
                $product->detail_url = $data[4];
                $product->price = (int)$data[5];
                $product->cv = (int)$data[6];
                $product->pv = (int)$data[7];
                $product->img_url = isset($data[8]) && $data[8] !== '' ? $data[8] : 'https://via.placeholder.com/400x400.png?text=No+Image';
                $product->save();
                $count++;
            }
        }
        fclose($handle);

        return response()->json(['message' => $count . '개의 상품이 일괄 등록되었습니다!']);
    }
}