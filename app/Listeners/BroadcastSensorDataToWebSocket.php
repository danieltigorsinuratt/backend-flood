<?php

namespace App\Listeners;

use App\Events\SensorDataReceived;

/**
 * Listener untuk sensor data events
 * 
 * Tidak perlu broadcast lagi karena SensorDataReceived event
 * sudah implement ShouldBroadcast.
 * Laravel Reverb akan handle broadcast otomatis.
 */
class BroadcastSensorDataToWebSocket
{
    /**
     * Handle the event.
     * 
     * Listener ini optional jika ada logic tambahan yang diperlukan.
     * Untuk sekarang biarkan kosong karena Reverb handle broadcast.
     */
    public function handle(SensorDataReceived $event): void
    {
        // Reverb sudah handle broadcasting otomatis
        // Logic tambahan bisa ditambahkan di sini jika diperlukan
    }
}
