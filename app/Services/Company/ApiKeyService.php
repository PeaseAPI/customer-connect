<?php

namespace App\Services\Company;

use App\Models\ApiKey;
use Illuminate\Support\Str;

class ApiKeyService
{
    /**
     * 列出用户的所有 API Key
     */
    public function list(int $companyId, int $userId = null, int $perPage = 15)
    {
        $query = ApiKey::where('company_id', $companyId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->with('user')->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * 创建 API Key
     */
    public function create(int $companyId, int $userId, array $data): ApiKey
    {
        $plainKey = ApiKey::generateKey();

        $apiKey = ApiKey::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'name' => $data['name'],
            'key' => hash('sha256', $plainKey),
            'permissions' => $data['permissions'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => true,
        ]);

        // 将原始 key 附加到响应中（仅此一次可见）
        $apiKey->plain_key = $plainKey;

        return $apiKey;
    }

    /**
     * 验证 API Key
     */
    public function validate(string $plainKey): ?ApiKey
    {
        $hashedKey = hash('sha256', $plainKey);

        $apiKey = ApiKey::where('key', $hashedKey)
            ->where('is_active', true)
            ->first();

        if (!$apiKey) {
            return null;
        }

        if (!$apiKey->isValid()) {
            return null;
        }

        // 更新最后使用时间
        $apiKey->update(['last_used_at' => now()]);

        return $apiKey;
    }

    /**
     * 吊销 API Key
     */
    public function revoke(ApiKey $apiKey): void
    {
        $apiKey->update(['is_active' => false]);
    }

    /**
     * 删除 API Key
     */
    public function delete(ApiKey $apiKey): void
    {
        $apiKey->delete();
    }
}
