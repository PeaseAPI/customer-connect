<?php

namespace Tests\Feature\Api;

use App\Models\ApprovalFlow;
use App\Models\ApprovalRecord;
use App\Models\ApprovalRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApprovalApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_approval_requests(): void
    {
        $flow = ApprovalFlow::factory()->create(['company_id' => $this->company->id]);
        ApprovalRequest::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'flow_id' => $flow->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson('/api/approvals');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_approval_request(): void
    {
        $flow = ApprovalFlow::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/approvals', [
            'flow_id' => $flow->id,
            'form_data' => ['type' => 'leave', 'from' => '2026-09-20', 'to' => '2026-09-22'],
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('approval_requests', [
            'flow_id' => $flow->id,
            'user_id' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_approval_without_required_fields(): void
    {
        $response = $this->postJson('/api/approvals', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['flow_id', 'form_data']);
    }

    public function test_can_show_approval_request(): void
    {
        $flow = ApprovalFlow::factory()->create(['company_id' => $this->company->id]);
        $request = ApprovalRequest::factory()->create([
            'company_id' => $this->company->id,
            'flow_id' => $flow->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/approvals/{$request->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $request->id]]);
    }

    public function test_can_list_pending_approvals(): void
    {
        $flow = ApprovalFlow::factory()->create(['company_id' => $this->company->id]);
        ApprovalRequest::factory()->create([
            'company_id' => $this->company->id,
            'flow_id' => $flow->id,
            'user_id' => $this->adminUser->id,
            'status' => 'pending',
        ]);

        // Create approval record for current user
        ApprovalRecord::create([
            'company_id' => $this->company->id,
            'request_id' => ApprovalRequest::first()->id,
            'approver_id' => $this->adminUser->id,
            'step' => 1,
            'action' => 'pending',
            'action' => 'pending',
        ]);

        $response = $this->getJson('/api/approvals/pending');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_approve_request(): void
    {
        $flow = ApprovalFlow::factory()->create(['company_id' => $this->company->id]);
        $approvalRequest = ApprovalRequest::factory()->create([
            'company_id' => $this->company->id,
            'flow_id' => $flow->id,
            'user_id' => User::factory()->create(['company_id' => $this->company->id])->id,
            'status' => 'pending',
            'current_step' => 1,
        ]);

        ApprovalRecord::create([
            'company_id' => $this->company->id,
            'request_id' => $approvalRequest->id,
            'approver_id' => $this->adminUser->id,
            'step' => 1,
            'action' => 'pending',
        ]);

        $response = $this->postJson("/api/approvals/{$approvalRequest->id}/approve", [
            'remark' => 'Approved - looks good',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_reject_request(): void
    {
        $flow = ApprovalFlow::factory()->create(['company_id' => $this->company->id]);
        $approvalRequest = ApprovalRequest::factory()->create([
            'company_id' => $this->company->id,
            'flow_id' => $flow->id,
            'user_id' => User::factory()->create(['company_id' => $this->company->id])->id,
            'status' => 'pending',
            'current_step' => 1,
        ]);

        ApprovalRecord::create([
            'company_id' => $this->company->id,
            'request_id' => $approvalRequest->id,
            'approver_id' => $this->adminUser->id,
            'step' => 1,
            'action' => 'pending',
        ]);

        $response = $this->postJson("/api/approvals/{$approvalRequest->id}/reject", [
            'remark' => 'Budget constraints',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('approval_requests', [
            'id' => $approvalRequest->id,
            'status' => 'rejected',
            'company_id' => $this->company->id,
        ]);
    }
}
