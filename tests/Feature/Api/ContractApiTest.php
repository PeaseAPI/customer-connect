<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected User $clientUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->clientUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_contracts(): void
    {
        Contract::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $this->clientUser->id,
        ]);

        $response = $this->getJson('/api/finance/contracts');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_contract(): void
    {
        $contractType = ContractType::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/finance/contracts', [
            'client_id' => $this->clientUser->id,
            'subject' => 'Service Agreement',
            'contract_type_id' => $contractType->id,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'value' => 50000,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('contracts', [
            'subject' => 'Service Agreement',
            'client_id' => $this->clientUser->id,
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_contract_without_client_id(): void
    {
        $response = $this->postJson('/api/finance/contracts', [
            'subject' => 'Test Contract',
            'start_date' => '2026-01-01',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('client_id');
    }

    public function test_cannot_create_contract_without_subject(): void
    {
        $response = $this->postJson('/api/finance/contracts', [
            'client_id' => $this->clientUser->id,
            'start_date' => '2026-01-01',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('subject');
    }

    public function test_can_show_contract(): void
    {
        $contract = Contract::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->clientUser->id,
        ]);

        $response = $this->getJson("/api/finance/contracts/{$contract->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_contract_status(): void
    {
        $contract = Contract::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->clientUser->id,
        ]);

        $response = $this->putJson("/api/finance/contracts/{$contract->id}", [
            'status' => 'active',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_contract(): void
    {
        $contract = Contract::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->clientUser->id,
        ]);

        $response = $this->deleteJson("/api/finance/contracts/{$contract->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_contract_store_generates_hash(): void
    {
        $response = $this->postJson('/api/finance/contracts', [
            'client_id' => $this->clientUser->id,
            'subject' => 'Test Hash',
            'start_date' => '2026-01-01',
        ]);

        $response->assertStatus(201);
                $this->assertDatabaseHas('contracts', [
            'subject' => 'Test Hash',
            'company_id' => $this->company->id,
        ]);
        $contract = Contract::where('subject', 'Test Hash')->first();
        $this->assertNotNull($contract->hash);
    }
}
