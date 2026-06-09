<?php

use App\Http\Controllers\CommandController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserDashboardLayoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/landing/chart-data', [DashboardController::class, 'landingChart']);

/**
 * Endpoint JSON dashboard — sama seperti monolith (session web + CSRF).
 * Di-proxy dari Next.js (/dashboard/*) agar widget layout berfungsi seperti sebelumnya.
 */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/dataset', [DashboardController::class, 'dataset']);
    Route::get('/dashboard/iot-connectivity', [DashboardController::class, 'iotConnectivity']);
    Route::get('/dashboard/firmware-api-host', [DashboardController::class, 'firmwareApiHost']);
    Route::get('/dashboard/kalender/data', [DashboardController::class, 'kalenderData']);
    Route::get('/dashboard/download/excel', [DashboardController::class, 'downloadExcel']);
    Route::post('/dashboard/riwayat/clear-data', [DashboardController::class, 'clearHistoryData']);
    Route::post('/dashboard/commands/send', [CommandController::class, 'send']);

    Route::get('/dashboard/user-layout', [UserDashboardLayoutController::class, 'show']);
    Route::post('/dashboard/user-layout', [UserDashboardLayoutController::class, 'store']);
    Route::delete('/dashboard/user-layout', [UserDashboardLayoutController::class, 'destroy']);
});
