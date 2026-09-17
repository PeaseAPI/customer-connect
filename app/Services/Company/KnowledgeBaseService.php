<?php

namespace App\Services\Company;

use App\Models\KnowledgeBase;

class KnowledgeBaseService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = KnowledgeBase::with(['category', 'creator']);

        if (!empty($filters['search'])) {
            $query->where('title', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): KnowledgeBase
    {
        return KnowledgeBase::create($data);
    }

    public function update(KnowledgeBase $knowledgeBase, array $data): KnowledgeBase
    {
        $knowledgeBase->update($data);
        return $knowledgeBase->fresh();
    }

    public function delete(KnowledgeBase $knowledgeBase): bool
    {
        return $knowledgeBase->delete();
    }
}
