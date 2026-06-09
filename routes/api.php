<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\Monitoring\CommandApiController;
use App\Http\Controllers\Api\Monitoring\DeviceApiController;
use App\Http\Controllers\Api\Monitoring\SensorDataApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\UserDashboardLayoutController;
use App\Http\Controllers\WaterLevelController;
use Illuminate\Support\Facades\Route;

// Publik — ringkasan ketinggian air (data database)
Route::get('/water-levels', [WaterLevelController::class, 'index']);
Route::get('/water-levels/{id}/history', [WaterLevelController::class, 'history']);

// IoT device (API key)
Route::middleware('apikey')->group(function () {
    Route::post('/ingest', [SensorController::class, 'ingest']);
    Route::get('/sensors', [SensorController::class, 'index']);

    Route::post('/command/send', [CommandController::class, 'send']);
    Route::get('/command/get', [CommandController::class, 'get']);
    Route::post('/command/done', [CommandController::class, 'done']);

    Route::get('/dashboard/data', [DashboardController::class, 'data']);
    Route::get('/dashboard/log', [DashboardController::class, 'log']);
});

// Auth (session / Sanctum SPA)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/register', [AuthApiController::class, 'register']);
    Route::post('/forgot-password', [AuthApiController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthApiController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthApiController::class, 'user']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);
    Route::post('/auth/confirm-password', [AuthApiController::class, 'confirmPassword']);
    Route::post('/auth/verification-notification', [AuthApiController::class, 'sendVerification']);

    Route::patch('/profile', [ProfileApiController::class, 'update']);
    Route::put('/profile/password', [ProfileApiController::class, 'updatePassword']);
    Route::delete('/profile', [ProfileApiController::class, 'destroy']);

    Route::prefix('dashboard')->group(function () {
        Route::get('/dataset', [DashboardController::class, 'dataset']);
        Route::get('/iot-connectivity', [DashboardController::class, 'iotConnectivity']);
        Route::get('/firmware-api-host', [DashboardController::class, 'firmwareApiHost']);
        Route::get('/kalender/data', [DashboardController::class, 'kalenderData']);
        Route::get('/download/excel', [DashboardController::class, 'downloadExcel']);
        Route::post('/commands/send', [CommandController::class, 'send']);
        Route::post('/riwayat/clear-data', [DashboardController::class, 'clearHistoryData']);

        Route::get('/user-layout', [UserDashboardLayoutController::class, 'show']);
        Route::post('/user-layout', [UserDashboardLayoutController::class, 'store']);
        Route::delete('/user-layout', [UserDashboardLayoutController::class, 'destroy']);
    });

    Route::middleware('role:admin')->prefix('monitoring')->group(function () {
        Route::get('/devices', [DeviceApiController::class, 'index']);
        Route::post('/devices', [DeviceApiController::class, 'store']);
        Route::get('/devices/{device}', [DeviceApiController::class, 'show']);
        Route::patch('/devices/{device}', [DeviceApiController::class, 'update']);
        Route::delete('/devices/{device}', [DeviceApiController::class, 'destroy']);

        Route::get('/sensor-data', [SensorDataApiController::class, 'index']);
        Route::delete('/sensor-data/{id}', [SensorDataApiController::class, 'destroy']);

        Route::get('/commands', [CommandApiController::class, 'index']);
        Route::post('/commands', [CommandApiController::class, 'store']);
        Route::post('/commands/{command}/executed', [CommandApiController::class, 'markExecuted']);
        Route::delete('/commands/{command}', [CommandApiController::class, 'destroy']);
    });
});
