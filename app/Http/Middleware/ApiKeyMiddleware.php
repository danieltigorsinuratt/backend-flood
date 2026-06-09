<?php

namespace App\Http\Middleware;

use App\Models\ApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('X-API-Key') ?? $request->query('api_key');

        if (! is_string($key) || $key === '') {
            return response()->json(['message' => 'API key tidak valid.'], 401);
        }

        $configuredKey = env('IOT_API_KEY');
        $valid = ($configuredKey !== null && $configuredKey !== '' && hash_equals((string) $configuredKey, $key))
            || ApiClient::query()->where('api_key', $key)->exists();

        if (! $valid) {
            return response()->json(['message' => 'API key tidak valid.'], 401);
        }

        return $next($request);
    }
}
