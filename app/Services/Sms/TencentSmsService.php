<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TencentSmsService implements SmsServiceInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'secret_id' => config('services.tencent_sms.secret_id'),
            'secret_key' => config('services.tencent_sms.secret_key'),
            'app_id' => config('services.tencent_sms.app_id'),
            'sign_name' => config('services.tencent_sms.sign_name'),
            'verify_template' => config('services.tencent_sms.verify_template'),
            'region' => config('services.tencent_sms.region', 'ap-guangzhou'),
        ];
    }

    public function sendVerificationCode(string $mobile, string $code): bool
    {
        $templateId = $this->config['verify_template'];

        return $this->sendSms($mobile, $templateId, [$code, '5']);
    }

    public function sendNotification(string $mobile, string $templateId, array $params): bool
    {
        return $this->sendSms($mobile, $templateId, array_values($params));
    }

    public function testConnection(): bool
    {
        return !empty($this->config['secret_id'])
            && !empty($this->config['secret_key'])
            && !empty($this->config['app_id'])
            && !empty($this->config['sign_name']);
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Send SMS（腾讯云短信API v2021-01-11）
     */
    private function sendSms(string $mobile, string $templateId, array $templateParams): bool
    {
        try {
            $host = 'sms.tencentcloudapi.com';
            $timestamp = time();
            $date = gmdate('Y-m-d', $timestamp);

            $payload = [
                'SmsSdkAppId' => $this->config['app_id'],
                'SignName' => $this->config['sign_name'],
                'TemplateId' => $templateId,
                'TemplateParamSet' => $templateParams,
                'PhoneNumberSet' => ['+86' . $mobile],
            ];

            $canonicalRequest = 'POST' . "\n"
                . '/' . "\n"
                . '' . "\n"
                . 'content-type:application/json; charset=utf-8' . "\n"
                . 'host:' . $host . "\n"
                . 'x-tc-action:SendSms' . "\n"
                . "\n"
                . 'content-type;host;x-tc-action' . "\n"
                . hash('SHA256', json_encode($payload));

            $credentialScope = $date . '/sms/tc3_request';
            $stringToSign = 'TC3-HMAC-SHA256' . "\n"
                . $timestamp . "\n"
                . $credentialScope . "\n"
                . hash('SHA256', $canonicalRequest);

            $secretDate = hash_hmac('SHA256', $date, 'TC3' . $this->config['secret_key'], true);
            $secretService = hash_hmac('SHA256', 'sms', $secretDate, true);
            $secretSigning = hash_hmac('SHA256', 'tc3_request', $secretService, true);
            $signature = hash_hmac('SHA256', $stringToSign, $secretSigning);

            $authorization = 'TC3-HMAC-SHA256 '
                . 'Credential=' . $this->config['secret_id'] . '/' . $credentialScope . ', '
                . 'SignedHeaders=content-type;host;x-tc-action, '
                . 'Signature=' . $signature;

            $response = Http::withHeaders([
                'Authorization' => $authorization,
                'Content-Type' => 'application/json; charset=utf-8',
                'Host' => $host,
                'X-TC-Action' => 'SendSms',
                'X-TC-Timestamp' => $timestamp,
                'X-TC-Version' => '2021-01-11',
                'X-TC-Region' => $this->config['region'],
            ])->post('https://' . $host, $payload);

            if ($response->successful()) {
                $result = $response->json();
                $status = $result['Response']['SendStatusSet'][0] ?? null;
                if ($status && ($status['Code'] ?? '') === 'Ok') {
                    return true;
                }
                Log::error('Tencent Cloud SMS send failed', ['result' => $result]);
                return false;
            }

            Log::error('Tencent Cloud SMS request failed', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Tencent Cloud SMS error', ['message' => $e->getMessage()]);
            return false;
        }
    }
}
