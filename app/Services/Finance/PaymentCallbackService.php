<?php

namespace App\Services\Finance;

use App\Models\PaymentCallback;

class PaymentCallbackService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = PaymentCallback::with(['payment']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): PaymentCallback
    {
        return PaymentCallback::create($data);
    }

    public function update(PaymentCallback $paymentCallback, array $data): PaymentCallback
    {
        $paymentCallback->update($data);
        return $paymentCallback->fresh();
    }

    public function delete(PaymentCallback $paymentCallback): bool
    {
        return $paymentCallback->delete();
    }
}
