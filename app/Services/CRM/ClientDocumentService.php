<?php

namespace App\Services\CRM;

use App\Models\ClientDocument;

class ClientDocumentService
{
    public function list(int $clientId, array $filters = [], int $perPage = 15)
    {
        $query = ClientDocument::where('client_id', $clientId)->with(['creator']);

        if (!empty($filters['search'])) {
            $query->where('document_name', 'like', "%{$filters['search']}%");
        }
        if (!empty($filters['document_type'])) {
            $query->where('document_type', $filters['document_type']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ClientDocument
    {
        return ClientDocument::create($data);
    }

    public function update(ClientDocument $document, array $data): ClientDocument
    {
        $document->update($data);
        return $document->fresh();
    }

    public function delete(ClientDocument $document): bool
    {
        return $document->delete();
    }
}
