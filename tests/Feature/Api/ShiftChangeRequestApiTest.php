<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\EmployeeShiftChangeRequest;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShiftChangeRequestApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->shift = Shift::factory()->create(['company_id' => $this->company->id]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_shift_change_requests(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        EmployeeShiftChangeRequest::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->getJson('/api/hrm/shift-change-requests');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_shift_change_request(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $currentShift = Shift::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/hrm/shift-change-requests', [
            'user_id' => $user->id,
            'shift_id' => $this->shift->id,
            'current_shift_id' => $currentShift->id,
            'effective_date' => '2026-10-01',
            'reason' => 'Personal preference',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('employee_shift_change_requests', [
            'user_id' => $user->id,
            'shift_id' => $this->shift->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_shift_change_request_without_required_fields(): void
    {
        $response = $this->postJson('/api/hrm/shift-change-requests', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'shift_id', 'effective_date']);
    }

    public function test_cannot_create_shift_change_request_with_invalid_shift(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/hrm/shift-change-requests', [
            'user_id' => $user->id,
            'shift_id' => 99999,
            'effective_date' => '2026-10-01',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('shift_id');
    }

    public function test_can_approve_shift_change_request(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $request = EmployeeShiftChangeRequest::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
            'shift_id' => $this->shift->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/hrm/shift-change-requests/{$request->id}/approve");

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('employee_shift_change_requests', [
            'id' => $request->id,
            'status' => 'approved',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_reject_shift_change_request(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $request = EmployeeShiftChangeRequest::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
            'shift_id' => $this->shift->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/hrm/shift-change-requests/{$request->id}/reject");

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('employee_shift_change_requests', [
            'id' => $request->id,
            'status' => 'rejected',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_shift_change_request(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $request = EmployeeShiftChangeRequest::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->deleteJson("/api/hrm/shift-change-requests/{$request->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('employee_shift_change_requests', ['id' => $request->id]);
    }
}
