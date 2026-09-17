<?php

namespace App\Services\CRM;

use App\Models\ClientContact;

class ClientContactService
{
    public function list(int $clientId, array $filters = [], int $perPage = 15)
    {
        $query = ClientContact::where('client_id', $clientId)->with(['creator']);

        if (!empty($filters['search'])) {
            $query->where('contact_name', 'like', "%{$filters['search']}%");
        }
        if (isset($filters['is_primary'])) {
            $query->where('is_primary', $filters['is_primary']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): ClientContact
    {
        return ClientContact::create($data);
    }

    public function update(ClientContact $contact, array $data): ClientContact
    {
        $contact->update($data);
        return $contact->fresh();
    }

    public function delete(ClientContact $contact): bool
    {
        return $contact->delete();
    }
}
