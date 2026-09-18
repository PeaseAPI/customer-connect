<?php

namespace App\Http\Controllers\Api;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use App\Services\Company\WebhookService;
use Illuminate\Http\Request;

class WebhookController extends BaseApiController
{
    public function __construct(protected WebhookService $webhookService) {}

    /**
     * 列出 Webhook
     */
    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $webhooks = $this->webhookService->list($companyId, $request->per_page ?? 15);
        return $this->paginated($webhooks);
    }

    /**
     * 创建 Webhook
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'string|max:100',
        ]);

        $companyId = $request->attributes->get('company_id');
        $webhook = $this->webhookService->create($companyId, $validated);

        return $this->success($webhook, 'Webhook Created successfully', 201);
    }

    /**
     * 查看详情
     */
    public function show(Webhook $webhook)
    {
        return $this->success($webhook);
    }

    /**
     * 更新 Webhook
     */
    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'url' => 'sometimes|url|max:500',
            'events' => 'sometimes|array|min:1',
            'events.*' => 'string|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $webhook = $this->webhookService->update($webhook, $validated);
        return $this->success($webhook, 'Updated successfully');
    }

    /**
     * 删除 Webhook
     */
    public function destroy(Webhook $webhook)
    {
        $this->webhookService->delete($webhook);
        return $this->success(null, 'Webhook deleted');
    }

    /**
     * 获取投递记录
     */
    public function deliveries(Request $request, Webhook $webhook)
    {
        $deliveries = $this->webhookService->getDeliveries(
            $webhook->id,
            $request->per_page ?? 15
        );
        return $this->paginated($deliveries);
    }

    /**
     * 重试失败的投递
     */
    public function retryDelivery(WebhookDelivery $delivery)
    {
        $this->webhookService->retryDelivery($delivery);
        return $this->success(null, 'Retry submitted');
    }
}
