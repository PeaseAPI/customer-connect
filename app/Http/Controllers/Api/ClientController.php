<?php

namespace App\Http\Controllers\Api;

use App\Exports\ClientExport;
use App\Imports\ClientImport;
use App\Jobs\ExportDataJob;
use App\Jobs\ImportDataJob;
use App\Models\User;
use App\Services\CRM\ClientService;
use Illuminate\Http\Request;

class ClientController extends BaseApiController
{
    public function __construct(protected ClientService $clientService) {}

    public function index(Request $request)
    {
        $clients = $this->clientService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($clients);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'company_name' => 'nullable|string|max:191',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:191',
            'note' => 'nullable|string',
            'skype' => 'nullable|string|max:100',
            'linkedin' => 'nullable|url|max:255',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        $client = $this->clientService->create($validated);

        return $this->success($client->load('clientDetail'), 'ClientCreated successfully', 201);
    }

    public function show(User $client)
    {
        return $this->success($client->load(['clientDetail', 'roles', 'clientContacts', 'clientNotes', 'clientDocuments']));
    }

    public function update(Request $request, User $client)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:191',
            'mobile' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:active,deactive',
            'company_name' => 'nullable|string|max:191',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:191',
            'note' => 'nullable|string',
            'skype' => 'nullable|string|max:100',
            'linkedin' => 'nullable|url|max:255',
        ]);

        $client = $this->clientService->update($client, $validated);

        return $this->success($client->load(['clientDetail', 'roles']), 'Updated successfully');
    }

    public function destroy(User $client)
    {
        $this->clientService->delete($client);
        return $this->success(null, 'Deleted successfully');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status', 'search']);
        $companyId = $request->attributes->get('company_id');
        $filePath = 'exports/clients_' . now()->format('YmdHis') . '.xlsx';

        ExportDataJob::dispatch(
            new ClientExport($filters, $companyId),
            $filePath,
            $request->user()->id
        );

        return $this->success(['file_path' => $filePath], 'Export task submitted, you will be notified when complete');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $path = $request->file('file')->store('imports');
        $companyId = $request->attributes->get('company_id');

        ImportDataJob::dispatch(
            new ClientImport($companyId),
            $path,
            $request->user()->id
        );

        return $this->success(null, 'Import task submitted, you will be notified when complete');
    }

    /**
     * 批量删除Client
     */
    public function batchDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:users,id',
        ]);

        $deleted = 0;
        foreach ($validated['ids'] as $id) {
            $client = User::find($id);
            if ($client && $client->hasRole('client')) {
                $this->clientService->delete($client);
                $deleted++;
            }
        }

        return $this->success(['deleted' => $deleted], "Deleted {$deleted} clients");
    }

    /**
     * 批量变更Client分类
     */
    public function batchChangeCategory(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:users,id',
            'category_id' => 'required|exists:client_categories,id',
        ]);

        $updated = 0;
        foreach ($validated['ids'] as $id) {
            $client = User::find($id);
            if ($client && $client->hasRole('client') && $client->clientDetail) {
                $client->clientDetail->update(['category_id' => $validated['category_id']]);
                $updated++;
            }
        }

        return $this->success(['updated' => $updated], "Updated {$updated} client categories");
    }

    /**
     * 批量变更ClientStatus
     */
    public function batchChangeStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|exists:users,id',
            'status' => 'required|in:active,deactive',
        ]);

        $updated = User::whereIn('id', $validated['ids'])
            ->whereHas('roles', fn($q) => $q->where('name', 'client'))
            ->update(['status' => $validated['status']]);

        return $this->success(['updated' => $updated], "Updated {$updated} client statuses");
    }
}
