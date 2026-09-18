<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductSubCategory;
use App\Services\Product\ProductSubCategoryService;
use Illuminate\Http\Request;

class ProductSubCategoryController extends BaseApiController
{
    public function __construct(protected ProductSubCategoryService $productSubCategoryService) {}

    public function index(Request $request, $categoryId)
    {
        $subCategories = $this->productSubCategoryService->list($categoryId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($subCategories);
    }

    public function store(Request $request, $categoryId)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['category_id'] = $categoryId;
        $validated['company_id'] = $request->attributes->get('company_id');
        $validated['added_by'] = $request->user()->id;

        return $this->success(
            $this->productSubCategoryService->create($validated),
            'Product sub-category created successfully',
            201
        );
    }

    public function show($categoryId, ProductSubCategory $sub_category)
    {
        return $this->success($sub_category);
    }

    public function update(Request $request, $categoryId, ProductSubCategory $sub_category)
    {
        $validated = $request->validate([
            'category_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $sub_category = $this->productSubCategoryService->update($sub_category, $validated);

        return $this->success($sub_category, 'Updated successfully');
    }

    public function destroy($categoryId, ProductSubCategory $sub_category)
    {
        $this->productSubCategoryService->delete($sub_category);

        return $this->success(null, 'Deleted successfully');
    }
}
