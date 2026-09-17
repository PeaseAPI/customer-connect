<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlipayService implements PaymentServiceInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'app_id' => config('services.alipay.app_id'),
            'private_key' => config('services.alipay.private_key'),
            'public_key' => config('services.alipay.public_key'),
            'sandbox' => config('services.alipay.sandbox', false),
            'notify_url' => config('services.alipay.notify_url', url('/api/payment/alipay/notify')),
            'return_url' => config('services.alipay.return_url', url('/payment/return')),
        ];
    }

    public function createOrder(array $orderData): array
    {
        try {
            $params = [
                'app_id' => $this->config['app_id'],
                'method' => 'alipay.trade.page.pay',
                'charset' => 'utf-8', 'sign_type' => 'RSA2',
                'timestamp' => now()->format('Y-m-d H:i:s'), 'version' => '1.0',
                'notify_url' => $this->config['notify_url'],
                'return_url' => $this->config['return_url'],
                'biz_content' => json_encode([
                    'out_trade_no' => $orderData['out_trade_no'],
                    'total_amount' => $orderData['total_amount'],
                    'subject' => $orderData['subject'],
                    'body' => $orderData['body'] ?? $orderData['subject'],
                    'product_code' => 'FAST_INSTANT_TRADE_PAY',
                ]),
            ];
            $params['sign'] = $this->sign($this->getSignContent($params));
            $gateway = $this->config['sandbox']
                ? 'https://openapi-sandbox.dl.alipaydev.com/gateway.do'
                : 'https://openapi.alipay.com/gateway.do';
            return [
                'pay_url' => $gateway . '?' . http_build_query($params),
                'out_trade_no' => $orderData['out_trade_no'],
                'gateway' => 'alipay',
            ];
        } catch (\Exception $e) {
            Log::error('支付宝创建订单失败', ['error' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function verifyCallback(array $callbackData): bool
    {
        try {
            $sign = $callbackData['sign'] ?? '';
            $signType = $callbackData['sign_type'] ?? 'RSA2';
            $data = $callbackData;
            unset($data['sign'], $data['sign_type']);
            ksort($data);
            $content = collect($data)->filter(fn($v) => $v !== '' && $v !== null)
                ->map(fn($v, $k) => $k . '=' . $v)->implode('&');
            $pubKey = "-----BEGIN PUBLIC KEY-----\n"
                . wordwrap($this->config['public_key'], 64, "\n", true) . "\n-----END PUBLIC KEY-----";
            $algo = $signType === 'RSA2' ? OPENSSL_ALGO_SHA256 : OPENSSL_ALGO_SHA1;
            return openssl_verify($content, base64_decode($sign), $pubKey, $algo) === 1;
        } catch (\Exception $e) {
            Log::error('支付宝回调验签失败', ['error' => $e->getMessage()]);
            return false;
        }
    }

    public function refund(string $transactionId, float $amount, string $reason = ''): array
    {
        try {
            $params = [
                'app_id' => $this->config['app_id'], 'method' => 'alipay.trade.refund',
                'charset' => 'utf-8', 'sign_type' => 'RSA2',
                'timestamp' => now()->format('Y-m-d H:i:s'), 'version' => '1.0',
                'biz_content' => json_encode([
                    'out_trade_no' => $transactionId,
                    'refund_amount' => $amount, 'refund_reason' => $reason,
                ]),
            ];
            $params['sign'] = $this->sign($this->getSignContent($params));
            $gateway = $this->config['sandbox']
                ? 'https://openapi-sandbox.dl.alipaydev.com/gateway.do'
                : 'https://openapi.alipay.com/gateway.do';
            $result = Http::asForm()->post($gateway, $params)->json();
            $r = $result['alipay_trade_refund_response'] ?? [];
            return ['success' => ($r['code'] ?? '') === '10000', 'trade_no' => $r['trade_no'] ?? '', 'refund_fee' => $r['refund_fee'] ?? 0];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function queryOrder(string $outTradeNo): array
    {
        try {
            $params = [
                'app_id' => $this->config['app_id'], 'method' => 'alipay.trade.query',
                'charset' => 'utf-8', 'sign_type' => 'RSA2',
                'timestamp' => now()->format('Y-m-d H:i:s'), 'version' => '1.0',
                'biz_content' => json_encode(['out_trade_no' => $outTradeNo]),
            ];
            $params['sign'] = $this->sign($this->getSignContent($params));
            $gateway = $this->config['sandbox']
                ? 'https://openapi-sandbox.dl.alipaydev.com/gateway.do'
                : 'https://openapi.alipay.com/gateway.do';
            return Http::asForm()->post($gateway, $params)->json();
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function testConnection(): bool
    {
        return !empty($this->config['app_id']) && !empty($this->config['private_key']) && !empty($this->config['public_key']);
    }

    public function getConfig(): array
    {
        return collect($this->config)->except(['private_key', 'public_key'])->toArray();
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    private function sign(string $data): string
    {
        $key = "-----BEGIN RSA PRIVATE KEY-----\n"
            . wordwrap($this->config['private_key'], 64, "\n", true) . "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($data, $sign, $key, OPENSSL_ALGO_SHA256);
        return base64_encode($sign);
    }

    private function getSignContent(array $params): string
    {
        unset($params['sign']);
        ksort($params);
        return collect($params)->filter(fn($v) => $v !== '' && $v !== null)
            ->map(fn($v, $k) => $k . '=' . $v)->implode('&');
    }
}
