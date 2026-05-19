<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\MapController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AuthController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public API endpoints (No auth needed for demo & mobile app)
Route::get('/api/latest-locations', [MapController::class, 'latest']);
Route::get('/api/devices', [MapController::class, 'devices']);
Route::get('/api/history/{device_id}', [MapController::class, 'history']);
Route::get('/api/stats', [MapController::class, 'stats']);
Route::get('/api/device-trails', [MapController::class, 'deviceTrails']);

// Secured Web Dashboard routes
Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect('/map');
    });

    Route::get('/map', [MapController::class, 'index']);
    Route::get('/devices', [MapController::class, 'devicesPage']);
    Route::get('/history', [MapController::class, 'historyPage']);

    // Service Kendaraan
    Route::get('/service/notifications', [ServiceController::class, 'notificationsPage']);
    Route::get('/vehicles', [ServiceController::class, 'vehiclesPage']);
    Route::get('/vehicles/create', [ServiceController::class, 'createVehicle']);
    Route::post('/vehicles', [ServiceController::class, 'storeVehicle']);
    Route::get('/vehicles/{id}', [ServiceController::class, 'vehicleDetail']);
    Route::get('/vehicles/{id}/edit', [ServiceController::class, 'editVehicle']);
    Route::put('/vehicles/{id}', [ServiceController::class, 'updateVehicle']);
    Route::delete('/vehicles/{id}', [ServiceController::class, 'deleteVehicle']);
    Route::get('/vehicles/{id}/service/create', [ServiceController::class, 'createServiceRecord']);
    Route::post('/vehicles/{id}/service', [ServiceController::class, 'storeServiceRecord']);
});
