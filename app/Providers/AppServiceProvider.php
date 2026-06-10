<?php

namespace App\Providers;

use App\Events\SensorDataReceived;
use App\Listeners\BroadcastSensorDataToWebSocket;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        $this->app['events']->listen(
            SensorDataReceived::class,
            BroadcastSensorDataToWebSocket::class
        );
    }
}
