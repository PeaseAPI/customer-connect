<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UnitTypeApiTest extends TestCase
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

    public function test_can_list_unit_types(): void
    {
        UnitType::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/finance/unit-types');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_unit_type(): void
    {
        $response = $this->postJson('/api/finance/unit-types', [
            'unit_type' => 'kilogram',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('unit_types', [
            'unit_type' => 'kilogram',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_unit_type_without_name(): void
    {
        $response = $this->postJson('/api/finance/unit-types', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('unit_type');
    }

    public function test_cannot_create_duplicate_unit_type(): void
    {
        UnitType::factory()->create(['company_id' => $this->company->id, 'unit_type' => 'gram']);

        $response = $this->postJson('/api/finance/unit-types', [
            'unit_type' => 'gram',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('unit_type');
    }

    public function test_can_show_unit_type(): void
    {
        $unitType = UnitType::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/finance/unit-types/{$unitType->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $unitType->id]]);
    }

    public function test_can_update_unit_type(): void
    {
        $unitType = UnitType::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/finance/unit-types/{$unitType->id}", [
            'unit_type' => 'liter',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('unit_types', [
            'id' => $unitType->id,
            'unit_type' => 'liter',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_unit_type(): void
    {
        $unitType = UnitType::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/finance/unit-types/{$unitType->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('unit_types', ['id' => $unitType->id]);
    }
}
