<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;

use App\Http\Controllers\Api\GpsController;

Route::post('/gps', [GpsController::class, 'store']);

Route::post('/sensor-data', function (Request $request) {
    // Menerima data dari Jembatan Serial
    $suhu = $request->input('suhu');
    $kelembaban = $request->input('kelembaban');

    // Catat ke Log Laravel (bisa dilihat di storage/logs/laravel.log)
    Log::info("Data masuk dari ESP32 via Serial:", ['suhu' => $suhu, 'kelembaban' => $kelembaban]);

    // Beri response sukses
    return response()->json([
        'status' => 'success',
        'message' => 'Data berhasil diterima sistem',
        'data' => [
            'suhu' => $suhu,
            'kelembaban' => $kelembaban
        ]
    ], 200);
});
