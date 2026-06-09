<?php

namespace Database\Seeders;

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@flood.local'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        );

        ApiClient::query()->updateOrCreate(
            ['name' => 'ESP32 Default'],
            ['api_key' => env('IOT_API_KEY', 'flood-iot-dev-key-'.Str::lower(Str::random(8)))],
        );
    }
}
