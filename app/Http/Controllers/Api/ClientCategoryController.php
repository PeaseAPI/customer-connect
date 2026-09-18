<?php

namespace App\Http\Controllers\Api;

use App\Models\ClientCategory;
use App\Services\CRM\ClientCategoryService;
use Illuminate\Http\Request;

class ClientCategoryController extends BaseApiController
{
    public function __construct(protected ClientCategoryService $clientCategoryService) {}

    public function index(Request $request)
    {
        $categories = $this->clientCategoryService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_name' => 'required|string|max:255',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->clientCategoryService->create($validated)->load(['subCategories']),
            '客户分类创建成功',
            201
        );
    }

    public function show(ClientCategory $clientCategory)
    {
        return $this->success($clientCategory->load(['subCategories']));
    }

    public function update(Request $request, ClientCategory $clientCategory)
    {
        $validated = $request->validate([
            'category_name' => 'sometimes|string|max:255',
        ]);

        $clientCategory = $this->clientCategoryService->update($clientCategory, $validated);

        return $this->success($clientCategory->load(['subCategories']), 'Updated successfully');
    }

    public function destroy(ClientCategory $clientCategory)
    {
        $this->clientCategoryService->delete($clientCategory);

        return $this->success(null, 'Deleted successfully');
    }
}
