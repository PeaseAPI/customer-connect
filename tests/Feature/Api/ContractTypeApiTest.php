<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\ContractType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractTypeApiTest extends TestCase
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

    public function test_can_list_contract_types(): void
    {
        ContractType::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/finance/contract-types');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_contract_type(): void
    {
        $response = $this->postJson('/api/finance/contract-types', [
            'name' => 'Fixed Term',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('contract_types', [
            'name' => 'Fixed Term',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_contract_type_without_name(): void
    {
        $response = $this->postJson('/api/finance/contract-types', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_can_show_contract_type(): void
    {
        $contractType = ContractType::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/finance/contract-types/{$contractType->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $contractType->id]]);
    }

    public function test_can_update_contract_type(): void
    {
        $contractType = ContractType::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/finance/contract-types/{$contractType->id}", [
            'name' => 'Updated Type',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('contract_types', [
            'id' => $contractType->id,
            'name' => 'Updated Type',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_contract_type(): void
    {
        $contractType = ContractType::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/finance/contract-types/{$contractType->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('contract_types', ['id' => $contractType->id]);
    }
}
