<?php

namespace App\Services\Finance;

use App\Models\RecurringInvoice;
use Illuminate\Support\Facades\DB;

class RecurringInvoiceService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = RecurringInvoice::with(['client', 'project', 'currency', 'creator']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): RecurringInvoice
    {
        return RecurringInvoice::create($data);
    }

    public function update(RecurringInvoice $recurringInvoice, array $data): RecurringInvoice
    {
        // Status changes should go through a dedicated method
        // when business logic is added in the future
        unset($data['status']);

        $recurringInvoice->update($data);
        return $recurringInvoice->fresh();
    }

    public function delete(RecurringInvoice $recurringInvoice): bool
    {
        return $recurringInvoice->delete();
    }
}

