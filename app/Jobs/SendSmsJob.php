<?php

namespace App\Jobs;

use App\Services\Sms\SmsManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public string $mobile,
        public string $type,
        public array $params = [],
        public ?string $driver = null
    ) {}

    public function handle(SmsManager $smsManager): void
    {
        try {
            match ($this->type) {
                'verification_code' => $smsManager->sendVerificationCode(
                    $this->mobile, $this->params['code'], $this->driver
                ),
                'notification' => $smsManager->sendNotification(
                    $this->mobile, $this->params['template_id'], $this->params['data'], $this->driver
                ),
                default => Log::warning("未知短信类型: {$this->type}"),
            };
        } catch (\Exception $e) {
            Log::error('短信发送Job失败', [
                'mobile' => $this->mobile,
                'type' => $this->type,
                'error' => $e->getMessage(),
            ]);
            $this->fail($e);
        }
    }
}
