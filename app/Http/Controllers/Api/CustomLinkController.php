<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomLink;
use App\Services\Company\CustomLinkService;
use Illuminate\Http\Request;

class CustomLinkController extends BaseApiController
{
    public function __construct(protected CustomLinkService $customLinkService) {}

    /**
     * 列出自定义链接
     */
    public function index(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $links = $this->customLinkService->list(
            $companyId,
            $request->section,
            $request->per_page ?? 15
        );
        return $this->paginated($links);
    }

    /**
     * 获取前端导航用的活跃链接
     */
    public function activeLinks(Request $request)
    {
        $companyId = $request->attributes->get('company_id');
        $links = $this->customLinkService->getActiveLinks(
            $companyId,
            $request->section ?? 'main'
        );
        return $this->success($links);
    }

    /**
     * Create custom link
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:191',
            'url' => 'required|url|max:500',
            'icon' => 'nullable|string|max:50',
            'target' => 'nullable|in:_blank,_self',
            'section' => 'nullable|in:main,settings,help',
            'sort_order' => 'nullable|integer',
        ]);

        $companyId = $request->attributes->get('company_id');
        $link = $this->customLinkService->create($companyId, $validated);

        return $this->success($link, 'Custom link created successfully', 201);
    }

    /**
     * Update custom link
     */
    public function update(Request $request, CustomLink $customLink)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:191',
            'url' => 'sometimes|url|max:500',
            'icon' => 'nullable|string|max:50',
            'target' => 'nullable|in:_blank,_self',
            'section' => 'nullable|in:main,settings,help',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $link = $this->customLinkService->update($customLink, $validated);
        return $this->success($link, 'Updated successfully');
    }

    /**
     * Delete custom link
     */
    public function destroy(CustomLink $customLink)
    {
        $this->customLinkService->delete($customLink);
        return $this->success(null, 'Custom link deleted');
    }

    /**
     * 批量排序
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:custom_links,id',
            'items.*.sort_order' => 'required|integer',
        ]);

        $this->customLinkService->reorder($validated['items']);
        return $this->success(null, 'Reorder updated successfully');
    }
}
