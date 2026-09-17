<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PromotionApiTest extends TestCase
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

    public function test_can_list_promotions(): void
    {
        Promotion::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->getJson('/api/hrm/promotions');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_promotion(): void
    {
        $department = Department::factory()->create(['company_id' => $this->company->id]);
        $designation = Designation::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/hrm/promotions', [
            'user_id' => $this->employee->id,
            'designation_id' => $designation->id,
            'department_id' => $department->id,
            'promotion_title' => 'Senior Developer',
            'promotion_date' => '2026-01-01',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('promotions', [
            'user_id' => $this->employee->id,
            'promotion_title' => 'Senior Developer',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_promotion_without_user_id(): void
    {
        $response = $this->postJson('/api/hrm/promotions', [
            'promotion_title' => 'Some Title',
            'promotion_date' => '2026-01-01',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }

    public function test_can_show_promotion(): void
    {
        $promotion = Promotion::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->getJson("/api/hrm/promotions/{$promotion->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $promotion->id]]);
    }

    public function test_can_update_promotion(): void
    {
        $promotion = Promotion::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->putJson("/api/hrm/promotions/{$promotion->id}", [
            'promotion_title' => 'Lead Developer',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('promotions', [
            'id' => $promotion->id,
            'promotion_title' => 'Lead Developer',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_promotion(): void
    {
        $promotion = Promotion::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->deleteJson("/api/hrm/promotions/{$promotion->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('promotions', ['id' => $promotion->id]);
    }

    public function test_promotion_store_sets_added_by(): void
    {
        $response = $this->postJson('/api/hrm/promotions', [
            'user_id' => $this->employee->id,
            'promotion_title' => 'Team Lead',
            'promotion_date' => '2026-01-01',
        ]);

        $response->assertStatus(201);
                $this->assertDatabaseHas('promotions', [
            'user_id' => $this->employee->id,
            'added_by' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }
}
