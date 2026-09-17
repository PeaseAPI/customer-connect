<?php

namespace App\Http\Controllers\Api;

use App\Models\ClientDocument;
use App\Services\CRM\ClientDocumentService;
use Illuminate\Http\Request;

class ClientDocumentController extends BaseApiController
{
    public function __construct(protected ClientDocumentService $clientDocumentService) {}

    public function index(Request $request, $clientId)
    {
        $documents = $this->clientDocumentService->list($clientId, $request->all(), $request->per_page ?? 15);

        return $this->paginated($documents);
    }

    public function store(Request $request, $clientId)
    {
        $validated = $request->validate([
            'document_name' => 'required|string|max:255',
            'document_type' => 'nullable|string|max:50',
            'filename' => 'nullable|string',
            'hashname' => 'nullable|string',
            'size' => 'nullable|integer',
            'external_link' => 'nullable|url',
        ]);

        $validated['client_id'] = $clientId;
        $validated['added_by'] = $request->user()->id;
        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->clientDocumentService->create($validated)->load(['creator']),
            '客户文档创建成功',
            201
        );
    }

    public function show($clientId, ClientDocument $document)
    {
        return $this->success($document->load(['creator']));
    }

    public function update(Request $request, $clientId, ClientDocument $document)
    {
        $validated = $request->validate([
            'document_name' => 'sometimes|string|max:255',
            'document_type' => 'nullable|string|max:50',
            'filename' => 'nullable|string',
            'hashname' => 'nullable|string',
            'size' => 'nullable|integer',
            'external_link' => 'nullable|url',
        ]);

        $validated['last_updated_by'] = $request->user()->id;
        $document = $this->clientDocumentService->update($document, $validated);

        return $this->success($document->load(['creator']), '更新成功');
    }

    public function destroy($clientId, ClientDocument $document)
    {
        $this->clientDocumentService->delete($document);

        return $this->success(null, '删除成功');
    }
}
