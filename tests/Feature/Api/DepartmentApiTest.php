<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DepartmentApiTest extends TestCase
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

    public function test_can_list_departments(): void
    {
        Department::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/hrm/departments');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_department(): void
    {
        $response = $this->postJson('/api/hrm/departments', [
            'department_name' => 'Engineering',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('departments', [
            'department_name' => 'Engineering',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_department_without_name(): void
    {
        $response = $this->postJson('/api/hrm/departments', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('department_name');
    }

    public function test_can_show_department(): void
    {
        $department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/hrm/departments/{$department->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_department(): void
    {
        $department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/hrm/departments/{$department->id}", [
            'department_name' => 'Updated Department',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_department(): void
    {
        $department = Department::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/hrm/departments/{$department->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
