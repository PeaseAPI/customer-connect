<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected Department $department;
    protected Designation $designation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->department = Department::factory()->create(['company_id' => $this->company->id]);
        $this->designation = Designation::factory()->create(['company_id' => $this->company->id]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_employees(): void
    {
        $employee = User::factory()->create(['company_id' => $this->company->id]);
        $employee->assignRole('employee');

        $response = $this->getJson('/api/hrm/employees');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_employee(): void
    {
        $response = $this->postJson('/api/hrm/employees', [
            'name' => 'Test Employee',
            'email' => 'employee@test.com',
            'password' => 'password123',
            'department_id' => $this->department->id,
            'designation_id' => $this->designation->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('users', [
            'email' => 'employee@test.com',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_employee_without_required_fields(): void
    {
        $response = $this->postJson('/api/hrm/employees', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password', 'department_id', 'designation_id']);
    }

    public function test_can_show_employee(): void
    {
        $employee = User::factory()->create(['company_id' => $this->company->id]);
        $employee->assignRole('employee');

        $response = $this->getJson("/api/hrm/employees/{$employee->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_employee(): void
    {
        $employee = User::factory()->create(['company_id' => $this->company->id]);
        $employee->assignRole('employee');

        $response = $this->putJson("/api/hrm/employees/{$employee->id}", [
            'name' => 'Updated Employee',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_employee(): void
    {
        $employee = User::factory()->create(['company_id' => $this->company->id]);
        $employee->assignRole('employee');

        $response = $this->deleteJson("/api/hrm/employees/{$employee->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
