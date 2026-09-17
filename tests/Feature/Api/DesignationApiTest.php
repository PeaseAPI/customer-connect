<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Designation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DesignationApiTest extends TestCase
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

    public function test_can_list_designations(): void
    {
        Designation::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/hrm/designations');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_designation(): void
    {
        $response = $this->postJson('/api/hrm/designations', [
            'designation_name' => 'Senior Engineer',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('designations', [
            'designation_name' => 'Senior Engineer',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_designation_without_name(): void
    {
        $response = $this->postJson('/api/hrm/designations', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('designation_name');
    }

        public function test_can_show_designation(): void
    {
        $designation = Designation::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/hrm/designations/{$designation->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

            public function test_can_update_designation(): void
    {
        $designation = Designation::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/hrm/designations/{$designation->id}", [
            'designation_name' => 'Updated Designation',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

            public function test_can_delete_designation(): void
    {
        $designation = Designation::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/hrm/designations/{$designation->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
