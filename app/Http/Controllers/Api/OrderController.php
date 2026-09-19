<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Finance\OrderService;
use App\Events\NewOrderPlaced;
use Illuminate\Http\Request;

class OrderController extends BaseApiController
{
    public function __construct(protected OrderService $orderService) {}

    public function index(Request $request)
    {
        $orders = $this->orderService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($orders);
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'client_id' => 'required|exists:users,id',
            'order_number' => 'required|string|max:255',
                        'status' => 'nullable|in:pending,processing,completed,canceled',
            'sub_total' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'total' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'currency_id' => 'nullable|exists:currencies,id',
            'date' => 'nullable|date',
            'note' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
        ]);
        $v['created_by'] = $request->user()->id;
        $v['company_id'] = $request->attributes->get('company_id');
        if (empty($v['date'])) {
            $v['date'] = now()->toDateString();
        }

        $order = $this->orderService->create($v);
        event(new NewOrderPlaced($order));
        return $this->success($order->load(['client', 'project', 'currency']), 'Order created successfully', 201);
    }

    public function show(Order $order)
    {
        return $this->success($order->load(['client', 'project', 'currency', 'items']));
    }

    public function update(Request $request, Order $order)
    {
        $v = $request->validate([
            'client_id' => 'sometimes|exists:users,id',
            'order_number' => 'sometimes|string|max:255',
            'status' => 'nullable|in:pending,processing,completed,canceled',
            'sub_total' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'total' => 'nullable|numeric',
            'tax' => 'nullable|numeric',
            'currency_id' => 'nullable|exists:currencies,id',
            'date' => 'nullable|date',
            'note' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
        ]);
        $v['last_updated_by'] = $request->user()->id;

        $order = $this->orderService->update($order, $v);

        return $this->success($order->load(['client', 'project', 'currency', 'items']), 'Updated successfully');
    }

    public function destroy(Order $order)
    {
        $this->orderService->delete($order);
        return $this->success(null, 'Deleted successfully');
    }
}
