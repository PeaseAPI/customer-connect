<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailTemplateController extends BaseApiController
{
    public function __construct(protected EmailTemplateService $emailTemplateService) {}

    public function index(Request $request): JsonResponse
    {
        $companyId = $request->attributes->get('company_id');
        $templates = $this->emailTemplateService->list($companyId, $request->all());
        return $this->paginated($templates);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'subject' => 'required|string|max:500',
            'body' => 'required|string',
            'variables' => 'nullable|array',
            'is_system' => 'boolean',
            'module' => 'nullable|string|max:100',
        ]);

        $companyId = $request->attributes->get('company_id');
        $template = $this->emailTemplateService->create($companyId, $validated);
        return $this->success($template, 'Email template created', 201);
    }

    public function show(EmailTemplate $emailTemplate): JsonResponse
    {
        return $this->success($emailTemplate);
    }

    public function update(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255',
            'subject' => 'sometimes|string|max:500',
            'body' => 'sometimes|string',
            'variables' => 'nullable|array',
            'module' => 'nullable|string|max:100',
        ]);

        $template = $this->emailTemplateService->update($emailTemplate, $validated);
        return $this->success($template, 'Email template updated');
    }

    public function destroy(EmailTemplate $emailTemplate): JsonResponse
    {
        $this->emailTemplateService->delete($emailTemplate);
        return $this->success(null, 'Email template deleted');
    }

    public function render(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $variables = $request->validate([
            'variables' => 'array',
        ]);

        $rendered = $this->emailTemplateService->render($emailTemplate, $variables['variables'] ?? []);
        return $this->success($rendered);
    }
}
