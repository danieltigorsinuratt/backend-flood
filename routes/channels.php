<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('sensor-channel', function () {
    return true;
});
