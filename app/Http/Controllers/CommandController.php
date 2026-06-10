<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Command;
use App\Models\ActivityLog;
use App\Services\WebSocketBroadcastService;

class CommandController extends Controller
{
    public function send(Request $request, WebSocketBroadcastService $webSocketService)
    {
        $request->validate([
            'device_id' => 'required|string',
            'command' => 'required|string',
        ]);

        $cmd = Command::create([
            'device_id' => $request->device_id,
            'command'   => $request->command,
            'status'    => 'pending',
        ]);

        ActivityLog::create([
            'device_id' => $request->device_id,
            'action'    => 'command_sent',
            'detail'    => $request->command
        ]);

        // Broadcast command to WebSocket
        $webSocketService->broadcastCommandExecution($request->device_id, [
            'id' => $cmd->id,
            'command' => $request->command,
            'status' => 'pending',
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json([
            'message' => 'Command queued',
            'id' => $cmd->id,
        ]);
    }

    public function get(Request $request)
    {
        $cmd = Command::where('device_id', $request->device_id)
                      ->where('status', 'pending')
                      ->first();
        return response()->json($cmd);
    }

    public function done(Request $request, WebSocketBroadcastService $webSocketService)
    {
        Command::where('id', $request->id)->update(['status' => 'executed']);

        ActivityLog::create([
            'device_id' => $request->device_id,
            'action'    => 'command_executed',
            'detail'    => 'Command ID: ' . $request->id
        ]);

        // Broadcast command execution status
        $webSocketService->broadcastCommandExecution($request->device_id, [
            'id' => $request->id,
            'status' => 'executed',
            'timestamp' => now()->toIso8601String(),
        ]);

        return response()->json(['message' => 'Command updated']);
    }

}