<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function __construct(protected ProductService $productService) {}

    public function index(Request $request)
    {
        $products = $this->productService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:191',
            'category_id' => 'nullable|exists:product_categories,id',
            'sub_category_id' => 'nullable|exists:product_sub_categories,id',
            'tax_id' => 'nullable|exists:tax_settings,id',
            'unit_id' => 'nullable|exists:unit_types,id',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->productService->create($validated)->load(['category', 'tax', 'creator']),
            'Product created successfully',
            201
        );
    }

    public function show(Product $product)
    {
        return $this->success($product->load(['category', 'tax', 'creator']));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'price' => 'sometimes|numeric',
            'description' => 'nullable|string',
            'sku' => 'nullable|string|max:191',
            'category_id' => 'nullable|exists:product_categories,id',
            'sub_category_id' => 'nullable|exists:product_sub_categories,id',
            'tax_id' => 'nullable|exists:tax_settings,id',
            'unit_id' => 'nullable|exists:unit_types,id',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $product = $this->productService->update($product, $validated);

        return $this->success($product->load(['category', 'tax', 'creator']), 'Updated successfully');
    }

    public function destroy(Product $product)
    {
        $this->productService->delete($product);
        return $this->success(null, 'Deleted successfully');
    }
}
