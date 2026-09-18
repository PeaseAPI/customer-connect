<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SmsTemplate;
use App\Services\SmsTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsTemplateController extends BaseApiController
{
    public function __construct(protected SmsTemplateService $smsTemplateService) {}

    public function index(Request $request): JsonResponse
    {
        $companyId = $request->attributes->get('company_id');
        $templates = $this->smsTemplateService->list($companyId, $request->all());
        return $this->paginated($templates);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'body' => 'required|string',
            'variables' => 'nullable|array',
            'is_system' => 'boolean',
            'module' => 'nullable|string|max:100',
        ]);

        $companyId = $request->attributes->get('company_id');
        $template = $this->smsTemplateService->create($companyId, $validated);
        return $this->success($template, 'SMS template created', 201);
    }

    public function show(SmsTemplate $smsTemplate): JsonResponse
    {
        return $this->success($smsTemplate);
    }

    public function update(Request $request, SmsTemplate $smsTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'body' => 'sometimes|string',
            'variables' => 'nullable|array',
            'module' => 'nullable|string|max:100',
        ]);

        $template = $this->smsTemplateService->update($smsTemplate, $validated);
        return $this->success($template, 'SMS template updated');
    }

    public function destroy(SmsTemplate $smsTemplate): JsonResponse
    {
        $this->smsTemplateService->delete($smsTemplate);
        return $this->success(null, 'SMS template deleted');
    }

    public function render(Request $request, SmsTemplate $smsTemplate): JsonResponse
    {
        $variables = $request->validate([
            'variables' => 'array',
        ]);

        $body = $this->smsTemplateService->render($smsTemplate, $variables['variables'] ?? []);
        return $this->success(['body' => $body]);
    }
}
