<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->deviceTokens()->active()->get();
        return $this->success($tokens);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string|unique:device_tokens,token',
            'platform' => 'required|string|in:ios,android,web',
            'device_name' => 'nullable|string|max:255',
            'app_version' => 'nullable|string|max:50',
        ]);

        // Deactivate any existing token for this device
        DeviceToken::where('token', $validated['token'])->update(['is_active' => false]);

        $token = $request->user()->deviceTokens()->create($validated);
        return $this->success($token, 'Device token registered', 201);
    }

    public function destroy(Request $request, DeviceToken $deviceToken): JsonResponse
    {
        if ($deviceToken->user_id !== $request->user()->id) {
            return $this->error('Unauthorized', 403);
        }

        $deviceToken->update(['is_active' => false]);
        return $this->success(null, 'Device token deactivated');
    }
}
