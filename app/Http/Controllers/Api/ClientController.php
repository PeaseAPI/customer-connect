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

        return $this->success($client->load('clientDetail'), '客户创建成功', 201);
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
            'status' => 'sometimes|in:active,inactive',
            'company_name' => 'nullable|string|max:191',
            'address' => 'nullable|string',
            'website' => 'nullable|url|max:191',
            'note' => 'nullable|string',
            'skype' => 'nullable|string|max:100',
            'linkedin' => 'nullable|url|max:255',
        ]);

        $client = $this->clientService->update($client, $validated);

        return $this->success($client->load(['clientDetail', 'roles']), '更新成功');
    }

    public function destroy(User $client)
    {
        $this->clientService->delete($client);
        return $this->success(null, '删除成功');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['status', 'search']);
        $filePath = 'exports/clients_' . now()->format('YmdHis') . '.xlsx';

        ExportDataJob::dispatch(
            new ClientExport($filters, $request->attributes->get('company_id')),
            $filePath,
            $request->user()
        );

        return $this->success(['file_path' => $filePath], '导出任务已提交，完成后将通知您');
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
            $request->user()
        );

        return $this->success(null, '导入任务已提交，完成后将通知您');
    }
}
