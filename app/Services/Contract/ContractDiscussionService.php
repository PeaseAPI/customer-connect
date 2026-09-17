<?php

namespace App\Services\Contract;

use App\Models\ContractDiscussion;

class ContractDiscussionService
{
    public function list(int $contractId, array $filters = [], int $perPage = 15)
    {
        $query = ContractDiscussion::where('contract_id', $contractId)
            ->with(['creator']);

        if (!empty($filters['search'])) {
            $query->where('comment', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ContractDiscussion
    {
        return ContractDiscussion::create($data);
    }

    public function update(ContractDiscussion $discussion, array $data): ContractDiscussion
    {
        $discussion->update($data);
        return $discussion->fresh();
    }

    public function delete(ContractDiscussion $discussion): bool
    {
        return $discussion->delete();
    }
}
