<?php

namespace App\Services\Finance;

use App\Models\Estimate;
use App\Models\EstimateItem;
use Illuminate\Support\Facades\DB;

class EstimateService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Estimate::with(['client', 'currency']);

        if (!empty($filters['search'])) {
            $query->where('estimate_number', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, array $items = []): Estimate
    {
        return DB::transaction(function () use ($data, $items) {
            $estimate = Estimate::create($data);

            foreach ($items as $item) {
                $estimate->items()->create([
                    'company_id' => $estimate->company_id,
                    'item_name' => $item['item_name'],
                    'item_summary' => $item['item_summary'] ?? null,
                    'quantity' => $item['quantity'] ?? 1,
                    'unit_price' => $item['unit_price'] ?? 0,
                    'amount' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                ]);
            }

            return $estimate;
        });
    }

    public function update(Estimate $estimate, array $data, ?array $items = null): Estimate
    {
        return DB::transaction(function () use ($estimate, $data, $items) {
            $estimate->update($data);

            // Replace items if provided
            if ($items !== null) {
                $estimate->items()->delete();
                foreach ($items as $item) {
                    $estimate->items()->create([
                        'company_id' => $estimate->company_id,
                        'item_name' => $item['item_name'],
                        'item_summary' => $item['item_summary'] ?? null,
                        'quantity' => $item['quantity'] ?? 1,
                        'unit_price' => $item['unit_price'] ?? 0,
                        'amount' => ($item['quantity'] ?? 1) * ($item['unit_price'] ?? 0),
                    ]);
                }
            }

            return $estimate->fresh();
        });
    }

    public function delete(Estimate $estimate): bool
    {
        return DB::transaction(function () use ($estimate) {
            $estimate->items()->delete();
            return $estimate->delete();
        });
    }

    public function send(Estimate $estimate): Estimate
    {
        $estimate->update(['status' => 'sent', 'sent_on' => now()]);
        return $estimate->fresh();
    }
}
