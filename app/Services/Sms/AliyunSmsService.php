<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AliyunSmsService implements SmsServiceInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'access_key_id' => config('services.aliyun_sms.access_key_id'),
            'access_key_secret' => config('services.aliyun_sms.access_key_secret'),
            'sign_name' => config('services.aliyun_sms.sign_name'),
            'verify_template' => config('services.aliyun_sms.verify_template'),
            'region' => config('services.aliyun_sms.region', 'cn-hangzhou'),
        ];
    }

    public function sendVerificationCode(string $mobile, string $code): bool
    {
        $templateId = $this->config['verify_template'];

        return $this->sendSms($mobile, $templateId, [
            'code' => $code,
            'time' => '5',
        ]);
    }

    public function sendNotification(string $mobile, string $templateId, array $params): bool
    {
        return $this->sendSms($mobile, $templateId, $params);
    }

    public function testConnection(): bool
    {
        return !empty($this->config['access_key_id'])
            && !empty($this->config['access_key_secret'])
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
     * Send SMS（阿里云短信API）
     */
    private function sendSms(string $mobile, string $templateId, array $templateParams): bool
    {
        try {
            $params = [
                'PhoneNumbers' => $mobile,
                'SignName' => $this->config['sign_name'],
                'TemplateCode' => $templateId,
                'TemplateParam' => json_encode($templateParams),
                'Action' => 'SendSms',
                'Version' => '2017-05-25',
                'Format' => 'JSON',
                'RegionId' => $this->config['region'],
                'AccessKeyId' => $this->config['access_key_id'],
                'SignatureMethod' => 'HMAC-SHA1',
                'SignatureVersion' => '1.0',
                'SignatureNonce' => uniqid(),
                'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            ];

            // 计算签名
            $params['Signature'] = $this->computeSignature($params);

            $response = Http::asForm()->post('https://dysmsapi.aliyuncs.com/', $params);

            if ($response->successful()) {
                $result = $response->json();
                if (($result['Code'] ?? '') === 'OK') {
                    return true;
                }
                Log::error('Alibaba Cloud SMS send failed', ['result' => $result]);
                return false;
            }

            Log::error('Alibaba Cloud SMS request failed', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Alibaba Cloud SMS error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * 计算阿里云API签名
     */
    private function computeSignature(array $params): string
    {
        ksort($params);
        $sortedQueryString = '';
        foreach ($params as $key => $value) {
            $sortedQueryString .= '&' . $this->encode($key) . '=' . $this->encode($value);
        }
        $stringToSign = 'POST&%2F&' . $this->encode(substr($sortedQueryString, 1));

        return base64_encode(hash_hmac('sha1', $stringToSign, $this->config['access_key_secret'] . '&', true));
    }

    private function encode(string $str): string
    {
        $res = urlencode($str);
        $res = str_replace(['+', '*', '%7E'], ['%20', '%2A', '~'], $res);

        return $res;
    }
}
