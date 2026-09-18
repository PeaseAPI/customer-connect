<?php

namespace App\Services\Finance;

use App\Enums\InvoiceStatus;
use App\Enums\ProposalStatus;
use App\Models\Proposal;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProposalService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Proposal::with(['client', 'currency', 'lead', 'deal']);

        if (!empty($filters['search'])) {
            $query->where('subject', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data, array $items = []): Proposal
    {
        return DB::transaction(function () use ($data, $items) {
            $subTotal = 0;
            foreach ($items as $item) {
                $qty = $item['quantity'] ?? 1;
                $price = $item['unit_price'] ?? 0;
                $subTotal += $qty * $price;
            }
            $data['sub_total'] = $subTotal;

            $discount = $data['discount'] ?? 0;
            $discountAmount = ($data['discount_type'] ?? 'percent') === 'percent'
                ? $subTotal * ($discount / 100) : $discount;
            $data['total'] = $subTotal - $discountAmount;

            $proposal = Proposal::create($data);

            foreach ($items as $item) {
                $proposal->items()->create([
                    'company_id' => $proposal->company_id,
                    'item_name' => $item['item_name'],
                    'item_summary' => $item['item_summary'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'amount' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                ]);
            }

            return $proposal;
        });
    }

    public function update(Proposal $proposal, array $data): Proposal
    {
        // Status changes must go through changeStatus() or send()
        unset($data['status']);

        $proposal->update($data);
        return $proposal->fresh();
    }

    public function changeStatus(Proposal $proposal, string $status): Proposal
    {
        $proposal->update(['status' => $status]);
        return $proposal->fresh();
    }

    public function delete(Proposal $proposal): bool
    {
        return $proposal->delete();
    }

    public function send(Proposal $proposal): Proposal
    {
                $proposal->update(['status' => ProposalStatus::Sent->value, 'send_status' => true]);
        return $proposal->fresh();
    }

    public function convertToInvoice(Proposal $proposal): Invoice
    {
        return DB::transaction(function () use ($proposal) {
            $invoice = Invoice::create([
                'company_id' => $proposal->company_id,
                'client_id' => $proposal->client_id,
                'invoice_number' => 'INV-' . str_pad(Invoice::max('id') + 1, 6, '0', STR_PAD_LEFT),
                'date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'sub_total' => $proposal->sub_total,
                'discount' => $proposal->discount,
                'discount_type' => $proposal->discount_type,
                'total' => $proposal->total,
                'currency_id' => $proposal->currency_id,
                                'status' => InvoiceStatus::Draft->value,
                'hash' => md5(uniqid(mt_rand(), true)),
                'note' => $proposal->note,
            ]);

            foreach ($proposal->items as $item) {
                $invoice->items()->create([
                    'company_id' => $item->company_id,
                    'invoice_id' => $invoice->id,
                    'item_name' => $item->item_name,
                    'item_summary' => $item->item_summary,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'amount' => $item->amount,
                ]);
            }

            $proposal->update(['invoice_convert' => true]);

            return $invoice;
        });
    }
}
