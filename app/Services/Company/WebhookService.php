<?php

namespace App\Services\Company;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Support\Facades\Context;

class WebhookService
{
    public function __construct(protected Http $http) {}

    /**
     * 列出 Webhook
     */
    public function list(int $companyId, int $perPage = 15)
    {
        return Webhook::where('company_id', $companyId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * 创建 Webhook
     */
    public function create(int $companyId, array $data): Webhook
    {
        $data['company_id'] = $companyId;
        return Webhook::create($data);
    }

    /**
     * 更新 Webhook
     */
    public function update(Webhook $webhook, array $data): Webhook
    {
        $webhook->update($data);
        return $webhook->fresh();
    }

    /**
     * 删除 Webhook
     */
    public function delete(Webhook $webhook): void
    {
        $webhook->delete();
    }

    /**
     * 触发 Webhook 事件
     */
    public function dispatchEvent(string $event, array $payload, ?int $companyId = null): void
    {
        $companyId = $companyId ?? Context::get('current_company_id');

        $webhooks = Webhook::where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($query) use ($event) {
                $query->whereJsonContains('events', '*')
                    ->orWhereJsonContains('events', $event);
            })
            ->get();

        foreach ($webhooks as $webhook) {
            $this->deliverWebhook($webhook, $event, $payload);
        }
    }

    /**
     * 投递 Webhook
     */
    protected function deliverWebhook(Webhook $webhook, string $event, array $payload): void
    {
        $delivery = WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event' => $event,
            'payload' => $payload,
            'status' => 'pending',
        ]);

        try {
            $startTime = microtime(true);

            $signature = $webhook->generateSignature($payload);

            $response = $this->http
                ->withHeaders([
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $event,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(10)
                ->post($webhook->url, $payload);

            $duration = (int) ((microtime(true) - $startTime) * 1000);

            $delivery->update([
                'response_status' => $response->status(),
                'response_body' => substr($response->body(), 0, 5000),
                'duration_ms' => $duration,
                'status' => $response->successful() ? 'success' : 'failed',
            ]);

            if ($response->successful()) {
                $webhook->resetFailureCount();
            } else {
                $webhook->recordFailure();
            }

            $webhook->update(['last_triggered_at' => now()]);
        } catch (\Exception $e) {
            $delivery->update([
                'response_body' => $e->getMessage(),
                'status' => 'failed',
            ]);

            $webhook->recordFailure();
        }
    }

    /**
     * 重试失败的投递
     */
    public function retryDelivery(WebhookDelivery $delivery): void
    {
        $webhook = $delivery->webhook;

        if (!$webhook || !$webhook->is_active) {
            return;
        }

        $this->deliverWebhook($webhook, $delivery->event, $delivery->payload);
    }

    /**
     * 获取投递记录
     */
    public function getDeliveries(int $webhookId, int $perPage = 15)
    {
        return WebhookDelivery::where('webhook_id', $webhookId)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }
}
