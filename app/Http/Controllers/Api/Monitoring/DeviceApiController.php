<?php

namespace App\Http\Controllers\Api\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        return response()->json(
            Device::query()->orderBy('device_id')->paginate($perPage)->withQueryString(),
        );
    }

    public function show(Device $device): JsonResponse
    {
        return response()->json($device);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:50', 'unique:devices,device_id'],
            'name' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:200'],
            'status' => ['required', 'in:online,offline'],
        ]);

        $device = Device::query()->create($validated);

        return response()->json([
            'message' => 'Perangkat dibuat.',
            'device' => $device,
        ], 201);
    }

    public function update(Request $request, Device $device): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:50', 'unique:devices,device_id,'.$device->id],
            'name' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:200'],
            'status' => ['required', 'in:online,offline'],
        ]);

        $device->update($validated);

        return response()->json([
            'message' => 'Perangkat diperbarui.',
            'device' => $device,
        ]);
    }

    public function destroy(Device $device): JsonResponse
    {
        $device->delete();

        return response()->json(['message' => 'Perangkat dihapus.']);
    }
}
