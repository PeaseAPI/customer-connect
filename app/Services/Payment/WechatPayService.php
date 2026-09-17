<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WechatPayService implements PaymentServiceInterface
{
    private array $config;

    public function __construct()
    {
        $this->config = [
            'mch_id' => config('services.wechat_pay.mch_id'),
            'api_key' => config('services.wechat_pay.api_key'),
            'cert_path' => config('services.wechat_pay.cert_path'),
            'key_path' => config('services.wechat_pay.key_path'),
            'notify_url' => config('services.wechat_pay.notify_url', url('/api/payment/wechat/notify')),
            'app_id' => config('services.wechat_pay.app_id', config('services.wechat_open.app_id')),
        ];
    }

    public function createOrder(array $orderData): array
    {
        try {
            $params = [
                'appid' => $this->config['app_id'],
                'mch_id' => $this->config['mch_id'],
                'nonce_str' => Str::random(32),
                'body' => $orderData['subject'],
                'out_trade_no' => $orderData['out_trade_no'],
                'total_fee' => (int) ($orderData['total_amount'] * 100),
                'spbill_create_ip' => request()->ip(),
                'notify_url' => $this->config['notify_url'],
                'trade_type' => $orderData['trade_type'] ?? 'NATIVE',
            ];
            $params['sign'] = $this->sign($params);
            $xml = $this->toXml($params);
            $result = $this->fromXml(
                Http::withBody($xml, 'text/xml')
                    ->post('https://api.mch.weixin.qq.com/pay/unifiedorder')->body()
            );
            if (($result['return_code'] ?? '') === 'SUCCESS' && ($result['result_code'] ?? '') === 'SUCCESS') {
                return [
                    'success' => true,
                    'code_url' => $result['code_url'] ?? '',
                    'prepay_id' => $result['prepay_id'] ?? '',
                    'mweb_url' => $result['mweb_url'] ?? '',
                    'out_trade_no' => $orderData['out_trade_no'],
                    'gateway' => 'wechat',
                ];
            }
            return ['success' => false, 'error' => $result['return_msg'] ?? '下单失败'];
        } catch (\Exception $e) {
            Log::error('微信支付创建订单异常', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyCallback(array $callbackData): bool
    {
        try {
            $sign = $callbackData['sign'] ?? '';
            unset($callbackData['sign']);
            return $sign === $this->sign($callbackData);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function refund(string $transactionId, float $amount, string $reason = ''): array
    {
        try {
            $params = [
                'appid' => $this->config['app_id'], 'mch_id' => $this->config['mch_id'],
                'nonce_str' => Str::random(32), 'out_trade_no' => $transactionId,
                'out_refund_no' => 'RF_' . $transactionId,
                'total_fee' => (int) ($amount * 100), 'refund_fee' => (int) ($amount * 100),
                'refund_desc' => $reason,
            ];
            $params['sign'] = $this->sign($params);
            $result = $this->fromXml(
                Http::withOptions([
                    'cert' => storage_path($this->config['cert_path']),
                    'ssl_key' => storage_path($this->config['key_path']),
                ])->withBody($this->toXml($params), 'text/xml')
                  ->post('https://api.mch.weixin.qq.com/secapi/pay/refund')->body()
            );
            return [
                'success' => ($result['result_code'] ?? '') === 'SUCCESS',
                'refund_id' => $result['refund_id'] ?? '',
                'refund_fee' => ($result['refund_fee'] ?? 0) / 100,
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function queryOrder(string $outTradeNo): array
    {
        $params = [
            'appid' => $this->config['app_id'], 'mch_id' => $this->config['mch_id'],
            'nonce_str' => Str::random(32), 'out_trade_no' => $outTradeNo,
        ];
        $params['sign'] = $this->sign($params);
        return $this->fromXml(
            Http::withBody($this->toXml($params), 'text/xml')
                ->post('https://api.mch.weixin.qq.com/pay/orderquery')->body()
        );
    }

    public function testConnection(): bool
    {
        return !empty($this->config['mch_id']) && !empty($this->config['api_key']);
    }

    public function getConfig(): array
    {
        return collect($this->config)->except(['api_key', 'cert_path', 'key_path'])->toArray();
    }

    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    private function sign(array $params): string
    {
        ksort($params);
        $str = collect($params)->filter(fn($v) => $v !== '' && $v !== null)
            ->map(fn($v, $k) => $k . '=' . $v)->implode('&');
        return strtoupper(md5($str . '&key=' . $this->config['api_key']));
    }

    private function toXml(array $data): string
    {
        $xml = '<xml>';
        foreach ($data as $k => $v) {
            $xml .= "<{$k}><![CDATA[{$v}]]></{$k}>";
        }
        return $xml . '</xml>';
    }

    private function fromXml(string $xml): array
    {
        return json_decode(json_encode(
            simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)
        ), true) ?? [];
    }
}
