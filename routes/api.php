<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;


Route::get('/gps', function (Request $request) {

    $lat = $request->lat;
    $lng = $request->lng;
    $deviceId = $request->device_id ?? 'IOT-DEV-01';

    if (!$lat || !$lng) {
        return response()->json(['status' => 'error', 'message' => 'Missing coordinates'], 400);
    }

    $data = [
        'device_id' => $deviceId,
        'latitude' => (float)$lat,
        'longitude' => (float)$lng,
        'speed' => (float)($request->speed ?? 0),
        'created_at' => now()->toIso8601String()
    ];

    $filePath = storage_path('gps.txt');
    
    // Append JSON line
    file_put_contents($filePath, json_encode($data) . "\n", FILE_APPEND);

    // Keep it small: Limit to last 2000 lines
    $lines = file($filePath);
    if (count($lines) > 2000) {
        $lines = array_slice($lines, -2000);
        file_put_contents($filePath, implode("", $lines));
    }

    return response()->json([
        'status' => 'ok',
        'data' => $data
    ]);

});
