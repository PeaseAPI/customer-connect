<?php

namespace App\Http\Controllers\Api;

use App\Models\Estimate;
use App\Services\Finance\EstimateService;
use Illuminate\Http\Request;

class EstimateController extends BaseApiController
{
    public function __construct(protected EstimateService $estimateService) {}

    public function index(Request $request)
    {
        $estimates = $this->estimateService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($estimates);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'sub_total' => 'required|numeric',
            'total' => 'required|numeric',
            'valid_till' => 'required|date',
            'date' => 'sometimes|date',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'currency_id' => 'nullable|exists:currencies,id',
            'note' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required_with:items|string',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.unit_price' => 'nullable|numeric',
        ]);

        $items = $validated['items'] ?? [];
        unset($validated['items']);

        $validated['estimate_number'] = 'EST-' . str_pad(Estimate::max('id') + 1, 6, '0', STR_PAD_LEFT);
        $validated['hash'] = md5(uniqid(mt_rand(), true));
        $validated['created_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        if (!isset($validated['date'])) {
            $validated['date'] = now()->format('Y-m-d');
        }

        $estimate = $this->estimateService->create($validated, $items);

        return $this->success($estimate->load(['client', 'currency', 'items']), 'Quote created successfully', 201);
    }

    public function show(Estimate $estimate)
    {
        return $this->success($estimate->load(['client', 'currency', 'items', 'creator']));
    }

    public function update(Request $request, Estimate $estimate)
    {
        $validated = $request->validate([
            'client_id' => 'sometimes|exists:users,id',
            'sub_total' => 'sometimes|numeric',
            'total' => 'sometimes|numeric',
            'valid_till' => 'sometimes|date',
            'date' => 'sometimes|date',
            'discount' => 'nullable|numeric',
            'discount_type' => 'nullable|in:percent,fixed',
            'currency_id' => 'nullable|exists:currencies,id',
            'note' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required_with:items|string',
            'items.*.quantity' => 'nullable|numeric',
            'items.*.unit_price' => 'nullable|numeric',
        ]);

        $items = $validated['items'] ?? null;
        unset($validated['items']);

        $validated['last_updated_by'] = $request->user()->id;

        $estimate = $this->estimateService->update($estimate, $validated, $items);

        return $this->success($estimate->load(['client', 'currency', 'items', 'creator']), 'Updated successfully');
    }

    public function destroy(Estimate $estimate)
    {
        $this->estimateService->delete($estimate);
        return $this->success(null, 'Deleted successfully');
    }

    public function send(Estimate $estimate)
    {
        $estimate = $this->estimateService->send($estimate);
        return $this->success($estimate, 'Quote sent');
    }
}
