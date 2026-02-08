<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MapController;

Route::get('/', function () {
    return redirect('/map');
});

// Pages
Route::get('/map', [MapController::class, 'index']);
Route::get('/devices', [MapController::class, 'devicesPage']);
Route::get('/history', [MapController::class, 'historyPage']);

// API (via web for simplicity, no auth needed for demo)
Route::get('/api/latest-locations', [MapController::class, 'latest']);
Route::get('/api/devices', [MapController::class, 'devices']);
Route::get('/api/history/{device_id}', [MapController::class, 'history']);
Route::get('/api/stats', [MapController::class, 'stats']);
