<?php

namespace App\Http\Controllers\Api\Monitoring;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Command;
use App\Models\Device;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommandApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);

        return response()->json([
            'commands' => Command::query()->latest()->paginate($perPage)->withQueryString(),
            'devices' => Device::query()->orderBy('name')->get(['device_id', 'name']),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'string', 'max:50'],
            'command' => ['required', 'string', 'in:start,stop,alert,reset,reboot'],
        ]);

        $command = Command::query()->create([
            'device_id' => $validated['device_id'],
            'command' => $validated['command'],
            'status' => 'pending',
        ]);

        ActivityLog::query()->create([
            'device_id' => $validated['device_id'],
            'action' => 'command_sent',
            'detail' => $validated['command'],
        ]);

        return response()->json([
            'message' => 'Command ditambahkan ke antrian.',
            'command' => $command,
        ], 201);
    }

    public function markExecuted(Command $command): JsonResponse
    {
        if ($command->status !== 'pending') {
            return response()->json(['message' => 'Command ini sudah bukan status pending.'], 422);
        }

        $command->update(['status' => 'executed']);

        ActivityLog::query()->create([
            'device_id' => $command->device_id,
            'action' => 'command_executed',
            'detail' => 'Command ID: '.$command->id,
        ]);

        return response()->json(['message' => 'Command ditandai executed.']);
    }

    public function destroy(Command $command): JsonResponse
    {
        $command->delete();

        return response()->json(['message' => 'Command dihapus.']);
    }
}
