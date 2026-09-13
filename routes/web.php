<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Reservation;
use App\Mail\ReservationReceived; 
use App\Mail\ReservationConfirmed;

Route::get('/', function () {
    return view('welcome');
});

// ----------------------------------------------------
// 🟢 [이메일 인증 공통 처리 로직]
// ----------------------------------------------------
$verifyHandler = function (Request $request, $id, $hash) {
    $user = User::find($id);

    if (! $user) {
        return redirect('http://localhost:5173/login?error=user_not_found');
    }

    // 1. 보안 토큰 검증
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        return redirect('http://localhost:5173/login?error=invalid_token');
    }

    // 2. DB email_verified_at 업데이트
    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    // 3. React 로그인 페이지로 리다이렉트
    return redirect('http://localhost:5173/login?verified=1');
};

// /api/ 유무에 상관없이 두 경로 모두 수신 처리
Route::get('/email/verify/{id}/{hash}', $verifyHandler)->name('verification.verify');
Route::get('/api/email/verify/{id}/{hash}', $verifyHandler);

// ----------------------------------------------------
// 🔴 [이메일 디자인 미리보기 라우트]
// ----------------------------------------------------
Route::get('/preview-email/received', function () {
    $reservation = Reservation::latest()->first();

    if (!$reservation) {
        $reservation = new Reservation([
            'name' => 'テスト(테스트)',
            'date' => '2026-09-10',
            'time' => '14:30',
            'phone' => '090-1234-5678',
            'menu_id' => 1
        ]);
    }

    return new ReservationReceived($reservation);
});

Route::get('/preview-email/confirmed', function () {
    $reservation = Reservation::latest()->first();

    if (!$reservation) {
        $reservation = new Reservation([
            'name' => 'テスト(테스트)',
            'date' => '2026-09-10',
            'time' => '14:30',
            'phone' => '090-1234-5678',
            'menu_id' => 1
        ]);
    }

    return new ReservationConfirmed($reservation);
});