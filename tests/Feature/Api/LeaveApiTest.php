<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeaveApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
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

        $this->leaveType = LeaveType::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_leaves(): void
    {
        Leave::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $response = $this->getJson('/api/hrm/leaves');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_leave(): void
    {
        $response = $this->postJson('/api/hrm/leaves', [
            'leave_type_id' => $this->leaveType->id,
            'leave_date' => '2026-02-15',
            'duration' => 'full',
            'reason' => 'Personal leave',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('leaves', [
            'leave_type_id' => $this->leaveType->id,
            'user_id' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_leave_without_type(): void
    {
        $response = $this->postJson('/api/hrm/leaves', [
            'leave_date' => '2026-02-15',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('leave_type_id');
    }

        public function test_can_show_leave(): void
    {
        $leave = Leave::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'leave_type_id' => $this->leaveType->id,
        ]);

        $response = $this->getJson("/api/hrm/leaves/{$leave->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_approve_leave(): void
    {
        $leave = Leave::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'leave_type_id' => $this->leaveType->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/hrm/leaves/{$leave->id}/approve");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'status' => 'approved',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_reject_leave(): void
    {
        $leave = Leave::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'leave_type_id' => $this->leaveType->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/hrm/leaves/{$leave->id}/reject");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('leaves', [
            'id' => $leave->id,
            'status' => 'rejected',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_filter_leaves_by_status(): void
    {
        Leave::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'leave_type_id' => $this->leaveType->id,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/hrm/leaves?status=pending');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
