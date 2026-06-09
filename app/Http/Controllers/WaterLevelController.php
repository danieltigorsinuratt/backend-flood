<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\SensorData;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Ringkasan ketinggian air per perangkat — data nyata dari database (bukan dummy).
 */
class WaterLevelController extends Controller
{
    public function index(): JsonResponse
    {
        $normalMax = SensorController::TH_NORMAL_MAX_CM;
        $siagaMax = SensorController::TH_SIAGA_MAX_CM;

        $devices = Device::query()->orderBy('device_id')->get();
        $latestByDevice = SensorData::query()
            ->selectRaw('device_id, MAX(id) as latest_id')
            ->groupBy('device_id')
            ->pluck('latest_id', 'device_id');

        $readings = SensorData::query()
            ->whereIn('id', $latestByDevice->values()->filter())
            ->get()
            ->keyBy('device_id');

        $levels = [];
        foreach ($devices as $device) {
            $reading = $readings->get($device->device_id);
            $value = $reading ? (float) $reading->water_level : null;
            $alert = $reading?->alert_level ?? 'normal';

            $levels[] = [
                'id' => $device->device_id,
                'sensorId' => $device->device_id,
                'label' => $device->name ?: $device->device_id,
                'value' => $value === null ? null : round($value, 2),
                'unit' => 'cm',
                'status' => $this->statusLabelFromAlert($alert),
                'alert_level' => $alert,
                'updated_at' => ($reading?->created_at ?? now())->toIso8601String(),
            ];
        }

        return response()->json([
            'levels' => $levels,
            'thresholds_cm' => [
                'normal_max' => $normalMax,
                'siaga_max' => $siagaMax,
            ],
            'updated_at' => now()->toIso8601String(),
        ]);
    }

    public function history(Request $request, string $id): JsonResponse
    {
        $limit = min(max((int) $request->query('limit', 60), 5), 200);

        $rows = SensorData::query()
            ->where('device_id', $id)
            ->latest()
            ->take($limit)
            ->get(['id', 'device_id', 'water_level', 'alert_level', 'created_at'])
            ->sortBy('created_at')
            ->values()
            ->map(fn (SensorData $row) => [
                'id' => $row->id,
                'device_id' => $row->device_id,
                'water_level' => round((float) $row->water_level, 2),
                'alert_level' => $row->alert_level,
                'created_at' => $row->created_at instanceof Carbon
                    ? $row->created_at->toIso8601String()
                    : (string) $row->created_at,
            ])
            ->all();

        return response()->json([
            'device_id' => $id,
            'history' => $rows,
        ]);
    }

    private function statusLabelFromAlert(string $alert): string
    {
        return match ($alert) {
            'danger' => 'BAHAYA',
            'warning' => 'SIAGA',
            default => 'NORMAL',
        };
    }
}
