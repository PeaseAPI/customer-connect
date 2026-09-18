<?php

namespace App\Services\Company;

use App\Models\CustomLink;

class CustomLinkService
{
    /**
     * 列出自定义链接
     */
    public function list(int $companyId, string $section = null, int $perPage = 15)
    {
        $query = CustomLink::where('company_id', $companyId);

        if ($section) {
            $query->where('section', $section);
        }

        return $query->orderBy('sort_order')->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * 获取活跃链接（用于前端导航）
     */
    public function getActiveLinks(int $companyId, string $section = 'main'): array
    {
        return CustomLink::where('company_id', $companyId)
            ->where('is_active', true)
            ->where('section', $section)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    /**
     * Create custom link
     */
    public function create(int $companyId, array $data): CustomLink
    {
        $data['company_id'] = $companyId;
        return CustomLink::create($data);
    }

    /**
     * Update custom link
     */
    public function update(CustomLink $link, array $data): CustomLink
    {
        $link->update($data);
        return $link->fresh();
    }

    /**
     * Delete custom link
     */
    public function delete(CustomLink $link): void
    {
        $link->delete();
    }

    /**
     * 批量排序
     */
    public function reorder(array $items): void
    {
        foreach ($items as $item) {
            CustomLink::where('id', $item['id'])->update([
                'sort_order' => $item['sort_order'] ?? 0,
            ]);
        }
    }
}
