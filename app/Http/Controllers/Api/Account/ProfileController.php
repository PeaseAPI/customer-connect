<?php

namespace App\Http\Controllers\Api\Account;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends BaseApiController
{
    public function show(Request $request): JsonResponse
    {
        return $this->success($request->user()->load(['company', 'roles']));
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => ['sometimes', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'avatar' => 'sometimes|string|max:500',
        ]);

        $user->update($validated);
        return $this->success($user->fresh());
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->error('当前密码不正确', 422);
        }

                $user->update(['password' => $validated['password']]);
        return $this->success(null, '密码修改成功');
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'locale' => 'sometimes|string|in:zh-cn,en',
            'timezone' => 'sometimes|string',
            'notification_settings' => 'sometimes|array',
            'notification_settings.email' => 'boolean',
            'notification_settings.database' => 'boolean',
            'notification_settings.sms' => 'boolean',
        ]);

        $user = $request->user();
        $user->update(['preferences' => array_merge($user->preferences ?? [], $validated)]);

        return $this->success($user->fresh());
    }
}
