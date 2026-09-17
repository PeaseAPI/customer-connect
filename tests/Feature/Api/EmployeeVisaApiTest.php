<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\EmployeeVisa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeVisaApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_employee_visas(): void
    {
        EmployeeVisa::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->getJson("/api/hrm/employees/{$this->employee->id}/visas");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_employee_visa(): void
    {
        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/visas", [
            'visa_type' => 'Work Visa',
            'visa_number' => 'V12345678',
            'issuing_country' => 'China',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('employee_visas', [
            'user_id' => $this->employee->id,
            'visa_type' => 'Work Visa',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_employee_visa_without_type(): void
    {
        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/visas", [
            'visa_number' => 'V12345678',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('visa_type');
    }

    public function test_can_show_employee_visa(): void
    {
        $visa = EmployeeVisa::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->getJson("/api/hrm/employees/{$this->employee->id}/visas/{$visa->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $visa->id]]);
    }

    public function test_can_update_employee_visa(): void
    {
        $visa = EmployeeVisa::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->putJson("/api/hrm/employees/{$this->employee->id}/visas/{$visa->id}", [
            'visa_type' => 'Business Visa',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('employee_visas', [
            'id' => $visa->id,
            'visa_type' => 'Business Visa',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_employee_visa(): void
    {
        $visa = EmployeeVisa::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->deleteJson("/api/hrm/employees/{$this->employee->id}/visas/{$visa->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('employee_visas', ['id' => $visa->id]);
    }

    public function test_employee_visa_store_sets_added_by(): void
    {
        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/visas", [
            'visa_type' => 'Tourist Visa',
        ]);

        $response->assertStatus(201);
                $this->assertDatabaseHas('employee_visas', [
            'user_id' => $this->employee->id,
            'added_by' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }
}
