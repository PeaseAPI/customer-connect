<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractRenewHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractRenewHistoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $client = User::factory()->create(['company_id' => $this->company->id]);
        $this->contract = Contract::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_contract_renew_histories(): void
    {
        ContractRenewHistory::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/finance/contracts/{$this->contract->id}/renew-histories");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_contract_renew_history(): void
    {
        $response = $this->postJson("/api/finance/contracts/{$this->contract->id}/renew-histories", [
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'amount' => 5000.00,
            'note' => 'Annual renewal',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('contract_renew_histories', [
            'contract_id' => $this->contract->id,
            'note' => 'Annual renewal',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_show_contract_renew_history(): void
    {
        $history = ContractRenewHistory::factory()->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/finance/contracts/{$this->contract->id}/renew-histories/{$history->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_contract_renew_history(): void
    {
        $history = ContractRenewHistory::factory()->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->putJson("/api/finance/contracts/{$this->contract->id}/renew-histories/{$history->id}", [
            'note' => 'Updated note',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_contract_renew_history(): void
    {
        $history = ContractRenewHistory::factory()->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/finance/contracts/{$this->contract->id}/renew-histories/{$history->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('contract_renew_histories', ['id' => $history->id]);
    }
}
