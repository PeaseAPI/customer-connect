<?php

namespace App\Http\Controllers\Api;

use App\Services\Payment\PaymentManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentCallbackController extends BaseApiController
{
    public function __construct(protected PaymentManager $paymentManager) {}

    /**
     * 支付宝异步回调
     */
    public function alipayNotify(Request $request): string
    {
        $data = $request->all();

        if ($this->paymentManager->verifyCallback('alipay', $data)) {
            $outTradeNo = $data['out_trade_no'];
            $tradeStatus = $data['trade_status'];

            if (in_array($tradeStatus, ['TRADE_SUCCESS', 'TRADE_FINISHED'])) {
                // 处理支付成功逻辑
                $this->handlePaymentSuccess($outTradeNo, 'alipay', $data['trade_no']);
            }

            return 'success';
        }

        return 'fail';
    }

    /**
     * 支付宝同步回调
     */
    public function alipayReturn(Request $request)
    {
        $data = $request->all();

        if ($this->paymentManager->verifyCallback('alipay', $data)) {
            return redirect()->away(config('app.frontend_url') . '/payment/success?' . http_build_query($data));
        }

        return redirect()->away(config('app.frontend_url') . '/payment/fail');
    }

    /**
     * 微信支付回调
     */
    public function wechatNotify(Request $request): \Illuminate\Http\Response
    {
        $xml = $request->getContent();
        $data = $this->xmlToArray($xml);

        if ($this->paymentManager->verifyCallback('wechat', $data)) {
            if (($data['result_code'] ?? '') === 'SUCCESS') {
                $this->handlePaymentSuccess($data['out_trade_no'], 'wechat', $data['transaction_id']);
            }

            return response('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>', 200)
                ->header('Content-Type', 'text/xml');
        }

        return response('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[Signature Error]]></return_msg></xml>', 200)
            ->header('Content-Type', 'text/xml');
    }

    private function handlePaymentSuccess(string $outTradeNo, string $gateway, string $transactionId): void
    {
        $payment = \App\Models\Payment::where('transaction_id', $outTradeNo)->first();

        if ($payment && $payment->status !== 'completed') {
            $payment->update([
                'status' => 'completed',
                'gateway' => $gateway,
                'gateway_transaction_id' => $transactionId,
                'paid_at' => now(),
            ]);
        }
    }

    private function xmlToArray(string $xml): array
    {
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data ?? [];
    }
}
