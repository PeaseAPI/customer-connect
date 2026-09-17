<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\EmployeeLeaveQuota;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeaveQuotaApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected User $employee;
    protected LeaveType $leaveType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->employee = User::factory()->create(['company_id' => $this->company->id]);
        $this->leaveType = LeaveType::factory()->create(['company_id' => $this->company->id]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_leave_quotas(): void
    {
        EmployeeLeaveQuota::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $response = $this->getJson("/api/hrm/employees/{$this->employee->id}/leave-quotas");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_leave_quota(): void
    {
        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/leave-quotas", [
            'leave_type_id' => $this->leaveType->id,
            'no_of_leaves' => 15,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('employee_leave_quotas', [
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'no_of_leaves' => 15,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_leave_quota_without_required_fields(): void
    {
        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/leave-quotas", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['leave_type_id', 'no_of_leaves']);
    }

    public function test_cannot_create_leave_quota_with_negative_leaves(): void
    {
        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/leave-quotas", [
            'leave_type_id' => $this->leaveType->id,
            'no_of_leaves' => -5,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('no_of_leaves');
    }

    public function test_can_update_leave_quota(): void
    {
        $quota = EmployeeLeaveQuota::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $response = $this->putJson("/api/hrm/employees/{$this->employee->id}/leave-quotas/{$quota->id}", [
            'no_of_leaves' => 20,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_leave_quota(): void
    {
        $quota = EmployeeLeaveQuota::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $response = $this->deleteJson("/api/hrm/employees/{$this->employee->id}/leave-quotas/{$quota->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('employee_leave_quotas', ['id' => $quota->id]);
    }

    public function test_can_adjust_leave_quota(): void
    {
        $quota = EmployeeLeaveQuota::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'no_of_leaves' => 10,
        ]);

        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/leave-quotas/{$quota->id}/adjust", [
            'action' => 'added',
            'amount' => 5,
            'reason' => 'Annual bonus',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_cannot_adjust_leave_quota_with_invalid_action(): void
    {
        $quota = EmployeeLeaveQuota::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/leave-quotas/{$quota->id}/adjust", [
            'action' => 'invalid_action',
            'amount' => 5,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('action');
    }
}
