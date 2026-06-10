<?php

namespace App\Listeners;

use App\Events\SensorDataReceived;
use App\Services\WebSocketBroadcastService;

class BroadcastSensorDataToWebSocket
{
    public function __construct(private WebSocketBroadcastService $webSocketService) {}

    /**
     * Handle the event.
     */
    public function handle(SensorDataReceived $event): void
    {
        $this->webSocketService->broadcastSensorData($event->payload);
    }
}
