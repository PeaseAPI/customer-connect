<?php

namespace App\Http\Controllers\Api;

use App\Models\ClientContact;
use App\Services\CRM\ClientContactService;
use Illuminate\Http\Request;

class ClientContactController extends BaseApiController
{
    public function __construct(protected ClientContactService $clientContactService) {}

    public function index(Request $request, $clientId)
    {
        $contacts = $this->clientContactService->list($clientId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($contacts);
    }

    public function store(Request $request, $clientId)
    {
        $validated = $request->validate([
            'contact_name' => 'required|string|max:255',
            'title' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'skype' => 'nullable|string|max:100',
            'linkedin' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
        ]);

        $validated['client_id'] = $clientId;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->clientContactService->create($validated)->load(['creator']),
            '客户联系人创建成功',
            201
        );
    }

    public function show($clientId, ClientContact $contact)
    {
        return $this->success($contact->load(['creator']));
    }

    public function update(Request $request, $clientId, ClientContact $contact)
    {
        $validated = $request->validate([
            'contact_name' => 'sometimes|string|max:255',
            'title' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'skype' => 'nullable|string|max:100',
            'linkedin' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_primary' => 'nullable|boolean',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $contact = $this->clientContactService->update($contact, $validated);

        return $this->success($contact->load(['creator']), '更新成功');
    }

    public function destroy($clientId, ClientContact $contact)
    {
        $this->clientContactService->delete($contact);

        return $this->success(null, '删除成功');
    }
}
