<?php

namespace App\Services\Finance;

use App\Models\CreditNote;
use Illuminate\Support\Facades\DB;

class CreditNoteService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = CreditNote::with(['client', 'invoice', 'currency']);

        if (!empty($filters['search'])) {
            $query->where('cn_number', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data, array $items = []): CreditNote
    {
        return DB::transaction(function () use ($data, $items) {
            $subTotal = 0;
            foreach ($items as $item) {
                $subTotal += ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0);
            }
            $data['sub_total'] = $subTotal;

            $discount = $data['discount'] ?? 0;
            $discountAmount = ($data['discount_type'] ?? 'percent') === 'percent'
                ? $subTotal * ($discount / 100) : $discount;
            $data['total'] = $subTotal - $discountAmount;

            $creditNote = CreditNote::create($data);

            foreach ($items as $item) {
                $creditNote->items()->create([
                    'company_id' => $creditNote->company_id,
                    'item_name' => $item['item_name'],
                    'item_summary' => $item['item_summary'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'amount' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                ]);
            }

            return $creditNote;
        });
    }

    public function update(CreditNote $creditNote, array $data): CreditNote
    {
        $creditNote->update($data);
        return $creditNote->fresh();
    }

    public function delete(CreditNote $creditNote): bool
    {
        return $creditNote->delete();
    }
}
