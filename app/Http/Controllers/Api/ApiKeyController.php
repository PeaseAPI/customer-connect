<?php

namespace App\Http\Controllers\Api;

use App\Models\ApiKey;
use App\Services\Company\ApiKeyService;
use Illuminate\Http\Request;

class ApiKeyController extends BaseApiController
{
    public function __construct(protected ApiKeyService $apiKeyService) {}

    /**
     * 列出 API Key
     */
    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $keys = $this->apiKeyService->list($companyId, null, $request->per_page ?? 15);
        return $this->paginated($keys);
    }

    /**
     * 创建 API Key
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|max:50',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $companyId = $request->attributes->get('company_id');
        $apiKey = $this->apiKeyService->create($companyId, $request->user()->id, $validated);

        return $this->success([
            'id' => $apiKey->id,
            'name' => $apiKey->name,
            'plain_key' => $apiKey->plain_key,
            'expires_at' => $apiKey->expires_at,
            'created_at' => $apiKey->created_at,
        ], 'API Key 创建成功，请妥善保管密钥，此为唯一一次可见', 201);
    }

    /**
     * 查看 API Key 详情
     */
    public function show(ApiKey $apiKey)
    {
        return $this->success($apiKey->load('user'));
    }

    /**
     * 吊销 API Key
     */
    public function revoke(ApiKey $apiKey)
    {
        $this->apiKeyService->revoke($apiKey);
        return $this->success(null, 'API Key 已吊销');
    }

    /**
     * 删除 API Key
     */
    public function destroy(ApiKey $apiKey)
    {
        $this->apiKeyService->delete($apiKey);
        return $this->success(null, 'API Key 已删除');
    }
}
