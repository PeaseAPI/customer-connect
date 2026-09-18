<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Enums\InvoiceStatus;
use App\Events\InvoiceCreated;
use App\Events\PaymentReceived;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Invoice::with(['client', 'project', 'items']);

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['start_date'])) {
            $query->where('date', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $query->where('date', '<=', $filters['end_date']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

        public function create(array $data, array $items = []): Invoice
    {
        return DB::transaction(function () use ($data, $items) {
            // If items were extracted from data by the controller, use the passed items
            // Otherwise, fall back to items within the data array
            if (empty($items) && isset($data['items'])) {
                $items = $data['items'];
            }
            unset($data['items']);

            $data['invoice_number'] = $data['invoice_number'] ?? $this->generateInvoiceNumber();
                        $data['status'] = $data['status'] ?? InvoiceStatus::Draft;
            $data['hash'] = $data['hash'] ?? Str::random(32);

            $invoice = Invoice::create($data);

            $totalAmount = 0;
            foreach ($items as $item) {
                $invoiceItem = $invoice->items()->create(array_merge($item, [
                    'company_id' => $invoice->company_id,
                ]));
                $totalAmount += $invoiceItem->amount ?? 0;
            }

            $discount = $invoice->discount ?? 0;
            $discountAmount = ($invoice->discount_type ?? 'percent') === 'percent'
                ? $totalAmount * ($discount / 100) : $discount;

            $invoice->update([
                'sub_total' => $totalAmount,
                'total' => $totalAmount - $discountAmount + $invoice->tax,
            ]);

            event(new InvoiceCreated($invoice));
            return $invoice->fresh();
        });
    }

        public function update(Invoice $invoice, array $data): Invoice
    {
        return DB::transaction(function () use ($invoice, $data) {
            // Status changes must go through send(), cancel(), or recordPayment()
            unset($data['status']);

            $items = $data['items'] ?? null;
            unset($data['items']);

            $invoice->update($data);

            if ($items !== null) {
                $invoice->items()->delete();
                $totalAmount = 0;
                foreach ($items as $item) {
                    $invoiceItem = $invoice->items()->create(array_merge($item, [
                        'company_id' => $invoice->company_id,
                    ]));
                    $totalAmount += $invoiceItem->amount ?? 0;
                }

                $discount = $invoice->discount ?? 0;
                $discountAmount = ($invoice->discount_type ?? 'percent') === 'percent'
                    ? $totalAmount * ($discount / 100) : $discount;

                $invoice->update([
                    'sub_total' => $totalAmount,
                    'total' => $totalAmount - $discountAmount + $invoice->tax,
                ]);
            }
            return $invoice->fresh();
        });
    }

    public function delete(Invoice $invoice): bool
    {
        return DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            return $invoice->delete();
        });
    }

    public function recordPayment(Invoice $invoice, array $paymentData): Payment
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            $payment = Payment::create(array_merge($paymentData, [
                'invoice_id' => $invoice->id,
                'company_id' => $invoice->company_id,
            ]));

                        $totalPaid = $invoice->payments()->sum('amount');
            if ($totalPaid >= $invoice->total) {
                $invoice->update(['status' => InvoiceStatus::Paid]);
            } elseif ($totalPaid > 0) {
                $invoice->update(['status' => InvoiceStatus::Partial]);
            }

            event(new PaymentReceived($payment));
            return $payment;
        });
    }

    public function send(Invoice $invoice): Invoice
    {
        return $this->sendInvoice($invoice);
    }

    public function cancel(Invoice $invoice): Invoice
    {
                $invoice->update(['status' => InvoiceStatus::Canceled]);
        return $invoice->fresh();
    }

    public function sendInvoice(Invoice $invoice): Invoice
    {
                $invoice->update(['status' => InvoiceStatus::Sent, 'sent_on' => now()]);
        return $invoice->fresh();
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $last = Invoice::whereDate('created_at', today())->count() + 1;
        return $prefix . '-' . $date . '-' . str_pad($last, 4, '0', STR_PAD_LEFT);
    }
}
