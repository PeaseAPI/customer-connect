<?php

namespace App\Http\Controllers\Api;

use App\Models\CustomField;
use App\Services\Company\CustomFieldService;
use Illuminate\Http\Request;

class CustomFieldController extends BaseApiController
{
    public function __construct(protected CustomFieldService $customFieldService) {}

    public function index(Request $request)
    {
        $fields = $this->customFieldService->list($request->all(), $request->per_page ?? 15);

        return $this->paginated($fields);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'field_name' => 'required|string|max:191',
            'field_type' => 'required|string|max:50',
            'field_options' => 'nullable|array',
            'module' => 'required|string|max:50',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');

        return $this->success(
            $this->customFieldService->create($validated),
            'Custom field created',
            201
        );
    }

    public function show(CustomField $customField)
    {
        return $this->success($customField);
    }

    public function update(Request $request, CustomField $customField)
    {
        $validated = $request->validate([
            'field_name' => 'sometimes|string|max:191',
            'field_type' => 'sometimes|string|max:50',
            'field_options' => 'nullable|array',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $customField = $this->customFieldService->update($customField, $validated);

        return $this->success($customField, 'Updated successfully');
    }

    public function destroy(CustomField $customField)
    {
        $this->customFieldService->delete($customField);

        return $this->success(null, 'Deleted successfully');
    }
}
