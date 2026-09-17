<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeaveTypeApiTest extends TestCase
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

    public function test_can_list_leave_types(): void
    {
        LeaveType::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/hrm/leave-types');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_leave_type(): void
    {
        $response = $this->postJson('/api/hrm/leave-types', [
            'type_name' => 'Annual Leave',
            'is_paid' => true,
            'paid_leaves' => 15,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('leave_types', [
            'type_name' => 'Annual Leave',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_leave_type_without_name(): void
    {
        $response = $this->postJson('/api/hrm/leave-types', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type_name');
    }

    public function test_can_show_leave_type(): void
    {
        $leaveType = LeaveType::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/hrm/leave-types/{$leaveType->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $leaveType->id]]);
    }

    public function test_can_update_leave_type(): void
    {
        $leaveType = LeaveType::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/hrm/leave-types/{$leaveType->id}", [
            'type_name' => 'Updated Leave Type',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('leave_types', [
            'id' => $leaveType->id,
            'type_name' => 'Updated Leave Type',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_leave_type(): void
    {
        $leaveType = LeaveType::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/hrm/leave-types/{$leaveType->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('leave_types', ['id' => $leaveType->id]);
    }
}
