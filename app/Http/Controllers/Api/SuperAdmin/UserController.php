<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseApiController
{
        public function index(Request $request): JsonResponse
    {
        $query = User::with(['company', 'roles']);

        if ($request->filled('keyword')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->keyword}%")
                  ->orWhere('email', 'like', "%{$request->keyword}%")
                  ->orWhere('mobile', 'like', "%{$request->keyword}%");
            });
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('is_super_admin')) {
            $query->whereHas('userAuths', fn($q) => $q->where('is_superadmin', $request->boolean('is_super_admin')));
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return $this->success($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['company', 'roles', 'permissions']);
        return $this->success($user);
    }

        public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'mobile' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $user->update($validated);
        return $this->success($user->fresh());
    }

        public function destroy(User $user): JsonResponse
    {
        if ($user->isSuperAdmin()) {
            return $this->error('无法删除超级管理员', 403);
        }
        $user->delete();
        return $this->success(null, '用户已删除');
    }

    public function resetPassword(User $user): JsonResponse
    {
        $newPassword = \Illuminate\Support\Str::random(12);
                $user->update(['password' => $newPassword]);

        return $this->success([
            'new_password' => $newPassword,
        ], '密码已重置');
    }
}
