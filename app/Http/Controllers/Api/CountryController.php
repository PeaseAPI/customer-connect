<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CountryController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Country::query();

        if ($request->has('search')) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }

        $countries = $query->orderBy('name')->get();
        return $this->success($countries);
    }

    public function show(Country $country): JsonResponse
    {
        return $this->success($country);
    }
}
