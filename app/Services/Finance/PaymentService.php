<?php

namespace App\Services\Finance;

use App\Models\Payment;
use App\Enums\InvoiceStatus;
use Illuminate\Support\Facades\DB;

class PaymentService
{
        public function list(array $filters = [], int $perPage = 15)
    {
        $query = Payment::with(['client', 'invoice', 'currency']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('invoice', fn ($i) => $i->where('invoice_number', 'like', "%{$filters['search']}%"))
                  ->orWhere('gateway', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['invoice_id'])) {
            $query->where('invoice_id', $filters['invoice_id']);
        }
        if (!empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['from_date'])) {
            $query->whereDate('paid_on', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('paid_on', '<=', $filters['to_date']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payment = Payment::create($data);

            // Update invoice status if linked
            if ($payment->invoice_id) {
                $invoice = $payment->invoice;
                $totalPaid = $invoice->payments()->sum('amount');
                                if ($totalPaid >= $invoice->total) {
                    $invoice->update(['status' => InvoiceStatus::Paid]);
                } elseif ($totalPaid > 0) {
                    $invoice->update(['status' => InvoiceStatus::Partial]);
                }
            }

            return $payment;
        });
    }

    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);
        return $payment->fresh();
    }

    public function delete(Payment $payment): bool
    {
        return $payment->delete();
    }
}
