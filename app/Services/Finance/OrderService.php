<?php

namespace App\Services\Finance;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Order::with(['client', 'project', 'currency', 'items']);

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        // Status changes should go through a dedicated changeStatus() method
        // when business logic (e.g., event dispatch) is added in the future
        unset($data['status']);

        $order->update($data);
        return $order->fresh();
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }
}
