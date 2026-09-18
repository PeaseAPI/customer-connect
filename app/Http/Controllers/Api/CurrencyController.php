<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Currency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Currency::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('currency_name', 'like', "%{$search}%")
                  ->orWhere('currency_code', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $currencies = $query->orderBy('currency_name')->get();
        return $this->success($currencies);
    }

    public function show(Currency $currency): JsonResponse
    {
        return $this->success($currency);
    }

    public function update(Request $request, Currency $currency): JsonResponse
    {
        $validated = $request->validate([
            'exchange_rate' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $currency->update($validated);
        return $this->success($currency, 'Currency updated');
    }
}
