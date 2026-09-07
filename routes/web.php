<?php

use Illuminate\Support\Facades\Route;
use App\Models\Reservation;
use App\Mail\ReservationReceived; 
use App\Mail\ReservationConfirmed; // 💡 만약 확정 클래스명이 ReservationConfirmedEmail 이라면 이름을 맞춰주세요!

Route::get('/', function () {
    return view('welcome');
});

// ----------------------------------------------------
// 🔴 [이메일 디자인 미리보기 라우트 추가]
// ----------------------------------------------------

// 1. [접수 완료] 메일 미리보기
Route::get('/preview-email/received', function () {
    $reservation = Reservation::latest()->first(); // 가장 최근에 들어온 실제 예약 데이터 가져오기

    // 만약 DB에 예약이 하나도 없다면 가짜 데이터를 보여줍니다.
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

// 2. [예약 확정] 메일 미리보기
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

    return new ReservationConfirmed($reservation); // 확정 메일 클래스명에 맞게 렌더링
});