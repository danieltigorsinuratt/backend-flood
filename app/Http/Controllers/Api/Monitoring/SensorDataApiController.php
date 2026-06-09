<?php

namespace App\Http\Controllers\Api\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SensorDataApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SensorData::query()->latest();

        if ($request->filled('device_id')) {
            $query->where('device_id', $request->string('device_id'));
        }
        if ($request->filled('alert_level')) {
            $query->where('alert_level', $request->string('alert_level'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }

        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        return response()->json([
            'sensorData' => $query->paginate($perPage)->withQueryString(),
            'devices' => Device::query()->orderBy('name')->get(['device_id', 'name']),
            'filters' => [
                'device_id' => $request->input('device_id', ''),
                'alert_level' => $request->input('alert_level', ''),
                'date_from' => $request->input('date_from', ''),
                'date_to' => $request->input('date_to', ''),
            ],
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        SensorData::query()->whereKey($id)->firstOrFail()->delete();

        return response()->json(['message' => 'Data sensor dihapus.']);
    }
}
