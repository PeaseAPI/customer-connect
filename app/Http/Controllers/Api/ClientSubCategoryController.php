<?php

namespace App\Http\Controllers\Api;

use App\Models\ClientSubCategory;
use App\Services\CRM\ClientSubCategoryService;
use Illuminate\Http\Request;

class ClientSubCategoryController extends BaseApiController
{
    public function __construct(protected ClientSubCategoryService $clientSubCategoryService) {}

    public function index(Request $request, $categoryId)
    {
        $subCategories = $this->clientSubCategoryService->list($categoryId, $request->all(), $request->per_page ?? 15);

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
            $this->clientSubCategoryService->create($validated),
            'Client sub-category created successfully',
            201
        );
    }

    public function show($categoryId, ClientSubCategory $sub_category)
    {
        return $this->success($sub_category);
    }

    public function update(Request $request, $categoryId, ClientSubCategory $sub_category)
    {
        $validated = $request->validate([
            'category_name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $sub_category = $this->clientSubCategoryService->update($sub_category, $validated);

        return $this->success($sub_category, 'Updated successfully');
    }

    public function destroy($categoryId, ClientSubCategory $sub_category)
    {
        $this->clientSubCategoryService->delete($sub_category);

        return $this->success(null, 'Deleted successfully');
    }
}
