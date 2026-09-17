<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductCategory;
use App\Services\Product\ProductCategoryService;
use Illuminate\Http\Request;

class ProductCategoryController extends BaseApiController
{
    public function __construct(protected ProductCategoryService $productCategoryService) {}

    public function index(Request $request)
    {
        $categories = $this->productCategoryService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->productCategoryService->create($validated)->load(['subCategories']),
            '产品分类创建成功',
            201
        );
    }

    public function show(ProductCategory $productCategory)
    {
        return $this->success($productCategory->load(['subCategories']));
    }

    public function update(Request $request, ProductCategory $productCategory)
    {
        $validated = $request->validate([
            'category_name' => 'sometimes|string|max:255',
        ]);

        $productCategory = $this->productCategoryService->update($productCategory, $validated);

        return $this->success($productCategory->load(['subCategories']), '更新成功');
    }

    public function destroy(ProductCategory $productCategory)
    {
        $this->productCategoryService->delete($productCategory);

        return $this->success(null, '删除成功');
    }
}
