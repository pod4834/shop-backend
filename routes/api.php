<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// 💡 통계 기능에 필요한 모델 추가
use App\Models\User;
use Illuminate\Support\Carbon;

// 💡 컨트롤러 연결
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminController; 
use App\Http\Controllers\Api\ReservationController; 
use App\Http\Controllers\GalleryController; // 💡 갤러리 컨트롤러 추가

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    return redirect('http://localhost:5174/login?verified=true');
})->name('verification.verify');

// ✅ 누구나 접근 가능한 공개 라우트 (로그인 전)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/products', [ProductController::class, 'index']); 
Route::get('/galleries', [GalleryController::class, 'index']); // 💡 누구나 갤러리 목록 조회 가능

Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// 🔴 고객 예약 관련 API
Route::get('/reservations/booked-times', [ReservationController::class, 'getBookedTimes']);
Route::post('/reservations', [ReservationController::class, 'store']);
Route::get('/reservations/{id}', [ReservationController::class, 'show']);
Route::put('/reservations/{id}', [ReservationController::class, 'update']);
Route::put('/reservations/{id}/cancel', [ReservationController::class, 'cancel']);

// ✅ 로그인한 사용자(관리자 포함)만 접근 가능한 보호된 라우트 (토큰 필요)
Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::put('/user/profile', [ProfileController::class, 'update']);
    Route::get('/user/shipping-addresses', [AddressController::class, 'index']);
    Route::post('/user/shipping-addresses', [AddressController::class, 'store']);
    Route::get('/user/cart', [CartController::class, 'show']); 
    Route::post('/user/cart', [CartController::class, 'update']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/checkout', [OrderController::class, 'checkout']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::post('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // 💡 갤러리 등록, 수정, 삭제 (관리자 권한)
    Route::post('/galleries', [GalleryController::class, 'store']);
    Route::put('/galleries/{id}', [GalleryController::class, 'update']);
    Route::delete('/galleries/{id}', [GalleryController::class, 'destroy']);

    Route::get('/admin/orders', [AdminController::class, 'getOrders']); 
    Route::put('/admin/orders/{id}/ship', [AdminController::class, 'shipOrder']); 
    Route::post('/admin/products', [AdminController::class, 'addProduct']); 
    Route::delete('/admin/products/{id}', [AdminController::class, 'deleteProduct']); 

    Route::get('/admin/reservations', [AdminController::class, 'getReservations']);
    Route::put('/admin/reservations/{id}/status', [AdminController::class, 'updateReservationStatus']);

    Route::get('/admin/email-templates', [\App\Http\Controllers\Api\AdminEmailTemplateController::class, 'index']);
    Route::put('/admin/email-templates/{id}', [\App\Http\Controllers\Api\AdminEmailTemplateController::class, 'update']);

    Route::get('/admin/users', [AdminController::class, 'getUsers']);
    Route::put('/admin/users/{id}/role', [AdminController::class, 'updateUserRole']);
    Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);

    Route::put('/admin/products/{id}/stock', [AdminController::class, 'updateStock']);
    Route::get('/admin/sales', [AdminController::class, 'getSalesSummary']);

    // 💡 [추가됨] 관리자 대시보드 통계 API (이번 달 가입자 & 누적 가입자)
    Route::get('/admin/stats', function () {
        $now = Carbon::now();
        
        $totalUsers = User::count();
        $thisMonthUsers = User::whereYear('created_at', $now->year)
                              ->whereMonth('created_at', $now->month)
                              ->count();

        return response()->json([
            'total' => $totalUsers,
            'thisMonth' => $thisMonthUsers
        ]);
    });
});