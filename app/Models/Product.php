<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // 프론트엔드에서 넘어오는 데이터 저장을 허용할 컬럼 목록
    protected $fillable = [
        'product_name', 
        'product_detail', 
        'price', 
        'image', 
        'category',
        'product_category',
        'description_title',
        'cv',
        'pv'
    ];
}