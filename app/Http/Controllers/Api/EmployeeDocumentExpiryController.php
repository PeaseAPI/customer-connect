<?php

namespace App\Http\Controllers\Api;

use App\Models\EmployeeDocumentExpiry;
use App\Services\HRM\EmployeeDocumentExpiryService;
use Illuminate\Http\Request;

class EmployeeDocumentExpiryController extends BaseApiController
{
    public function __construct(protected EmployeeDocumentExpiryService $documentExpiryService) {}

    public function index(Request $request)
    {
        $expiries = $this->documentExpiryService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($expiries);
    }

    public function show(EmployeeDocumentExpiry $documentExpiry)
    {
        return $this->success($documentExpiry->load(['document']));
    }

    public function update(Request $request, EmployeeDocumentExpiry $documentExpiry)
    {
        $validated = $request->validate([
            'expiry_date' => 'sometimes|date',
            'notified' => 'nullable|boolean',
        ]);

        $documentExpiry = $this->documentExpiryService->update($documentExpiry, $validated);

        return $this->success($documentExpiry->load(['document']), '更新成功');
    }

    public function destroy(EmployeeDocumentExpiry $documentExpiry)
    {
        $this->documentExpiryService->delete($documentExpiry);

        return $this->success(null, '删除成功');
    }
}
