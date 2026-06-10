<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WebSocketBroadcastService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('WEBSOCKET_API_URL', 'http://localhost:6001');
    }

    /**
     * Broadcast data ke WebSocket channel
     *
     * @param string $channel Nama channel
     * @param array $data Data yang akan dikirim
     * @return bool Success status
     */
    public function broadcast(string $channel, array $data): bool
    {
        try {
            $response = Http::timeout(5)->post("{$this->baseUrl}/api/broadcast", [
                'channel' => $channel,
                'data' => $data,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            \Log::warning("WebSocket broadcast failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Broadcast sensor data ke channel
     */
    public function broadcastSensorData(array $sensorData): bool
    {
        return $this->broadcast('sensor-channel', [
            'type' => 'sensor.updated',
            'payload' => $sensorData,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast device status
     */
    public function broadcastDeviceStatus(string $deviceId, string $status): bool
    {
        return $this->broadcast('device-channel', [
            'type' => 'device.status_changed',
            'device_id' => $deviceId,
            'status' => $status,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Broadcast command execution
     */
    public function broadcastCommandExecution(string $deviceId, array $command): bool
    {
        return $this->broadcast('command-channel', [
            'type' => 'command.executed',
            'device_id' => $deviceId,
            'command' => $command,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
