<?php

namespace App\Services;

use App\Events\SensorDataReceived;

/**
 * Service untuk trigger real-time broadcasts via Laravel Reverb
 * 
 * Reverb menangani WebSocket communication langsung.
 * Service ini hanya helper untuk trigger events.
 */
class WebSocketBroadcastService
{
    /**
     * Broadcast sensor data update
     */
    public function broadcastSensorData(array $sensorData): void
    {
        SensorDataReceived::dispatch($sensorData);
    }

    /**
     * Broadcast device status change
     */
    public function broadcastDeviceStatus(string $deviceId, string $status): void
    {
        broadcast(new class($deviceId, $status) implements \Illuminate\Contracts\Broadcasting\ShouldBroadcast {
            use \Illuminate\Broadcasting\InteractsWithSockets, \Illuminate\Queue\SerializesModels;

            public function __construct(public string $deviceId, public string $status) {}

            public function broadcastOn(): array
            {
                return [\Illuminate\Broadcasting\Channel::class => 'device-channel'];
            }

            public function broadcastAs(): string
            {
                return 'device.status_changed';
            }

            public function broadcastWith(): array
            {
                return [
                    'device_id' => $this->deviceId,
                    'status' => $this->status,
                    'timestamp' => now()->toIso8601String(),
                ];
            }
        })->toOthers();
    }

    /**
     * Broadcast command execution
     */
    public function broadcastCommandExecution(string $deviceId, array $command): void
    {
        broadcast(new class($deviceId, $command) implements \Illuminate\Contracts\Broadcasting\ShouldBroadcast {
            use \Illuminate\Broadcasting\InteractsWithSockets, \Illuminate\Queue\SerializesModels;

            public function __construct(public string $deviceId, public array $command) {}

            public function broadcastOn(): array
            {
                return [\Illuminate\Broadcasting\Channel::class => 'command-channel'];
            }

            public function broadcastAs(): string
            {
                return 'command.executed';
            }

            public function broadcastWith(): array
            {
                return [
                    'device_id' => $this->deviceId,
                    'command' => $this->command,
                    'timestamp' => now()->toIso8601String(),
                ];
            }
        })->toOthers();
    }
}

