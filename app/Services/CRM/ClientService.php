<?php

namespace App\Services\CRM;

use App\Models\User;
use App\Models\ClientContact;
use App\Events\ClientCreated;
use Illuminate\Support\Facades\DB;

class ClientService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = User::with(['clientDetail', 'roles', 'clientContacts', 'clientNotes', 'clientDocuments'])
            ->whereHas('roles', fn($q) => $q->where('name', 'client'));

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('mobile', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

        public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Separate user fields from client detail fields
            $userFields = collect($data)->only([
                'name', 'email', 'mobile', 'password', 'company_id', 'status', 'image', 'gender',
            ])->toArray();

            $detailFields = collect($data)->only([
                'company_name', 'address', 'website', 'note', 'shipping_address',
                'category_id', 'sub_category_id',
            ])->toArray();

            // Password is auto-hashed by User model's 'hashed' cast
            $client = User::create($userFields);
            $client->assignRole('client');

            if (!empty($detailFields)) {
                $client->clientDetail()->create(array_merge($detailFields, [
                    'company_id' => $client->company_id,
                ]));
            }

            event(new ClientCreated($client));
            return $client;
        });
    }

        public function update(User $client, array $data): User
    {
        return DB::transaction(function () use ($client, $data) {
            $userFields = collect($data)->only([
                'name', 'mobile', 'status', 'image', 'gender',
            ])->toArray();

            $detailFields = collect($data)->only([
                'company_name', 'address', 'website', 'note', 'shipping_address',
                'category_id', 'sub_category_id',
            ])->filter()->toArray();

            if (!empty($userFields)) {
                $client->update($userFields);
            }

            if (!empty($detailFields)) {
                $client->clientDetail?->update($detailFields);
            }

            return $client->fresh();
        });
    }

    public function delete(User $client): bool
    {
        return $client->delete();
    }

    public function addContact(User $client, array $data): ClientContact
    {
        return $client->clientContacts()->create(array_merge($data, [
            'company_id' => $client->company_id,
        ]));
    }

    public function updateContact(ClientContact $contact, array $data): ClientContact
    {
        $contact->update($data);
        return $contact->fresh();
    }

    public function deleteContact(ClientContact $contact): bool
    {
        return $contact->delete();
    }
}
