<?php

namespace Tests\Feature\Api;

use App\Models\Appreciation;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AppreciationApiTest extends TestCase
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

    public function test_can_list_appreciations(): void
    {
        $employee = User::factory()->create(['company_id' => $this->company->id]);
        Appreciation::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $employee->id,
        ]);

        $response = $this->getJson('/api/hrm/appreciations');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_appreciation(): void
    {
        $employee = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/hrm/appreciations', [
            'user_id' => $employee->id,
            'description' => 'Excellent work on the project',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('appreciations', [
            'user_id' => $employee->id,
            'description' => 'Excellent work on the project',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_appreciation_without_user_id(): void
    {
        $response = $this->postJson('/api/hrm/appreciations', [
            'description' => 'Some description',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }

    public function test_can_show_appreciation(): void
    {
        $appreciation = Appreciation::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/hrm/appreciations/{$appreciation->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $appreciation->id]]);
    }

    public function test_can_update_appreciation(): void
    {
        $appreciation = Appreciation::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/hrm/appreciations/{$appreciation->id}", [
            'description' => 'Updated description',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('appreciations', [
            'id' => $appreciation->id,
            'description' => 'Updated description',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_appreciation(): void
    {
        $appreciation = Appreciation::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/hrm/appreciations/{$appreciation->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('appreciations', ['id' => $appreciation->id]);
    }

    public function test_appreciation_store_sets_awarded_by(): void
    {
        $employee = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/hrm/appreciations', [
            'user_id' => $employee->id,
            'description' => 'Great work',
        ]);

        $response->assertStatus(201);
                $this->assertDatabaseHas('appreciations', [
            'user_id' => $employee->id,
            'awarded_by' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }
}
