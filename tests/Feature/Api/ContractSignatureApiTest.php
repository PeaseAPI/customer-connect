<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractSignature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractSignatureApiTest extends TestCase
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

    public function test_can_list_contract_signatures(): void
    {
        ContractSignature::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'signed_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/finance/contracts/{$this->contract->id}/signatures");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_contract_signature(): void
    {
        $response = $this->postJson("/api/finance/contracts/{$this->contract->id}/signatures", [
            'signed_by' => $this->adminUser->id,
            'signature' => 'data:image/png;base64,signaturedata',
            'signed_date' => '2026-09-14',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('contract_signatures', [
            'contract_id' => $this->contract->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_signature_without_required_fields(): void
    {
        $response = $this->postJson("/api/finance/contracts/{$this->contract->id}/signatures", []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['signed_by', 'signature', 'signed_date']);
    }

    public function test_can_show_contract_signature(): void
    {
        $signature = ContractSignature::factory()->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'signed_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/finance/contracts/{$this->contract->id}/signatures/{$signature->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_contract_signature(): void
    {
        $signature = ContractSignature::factory()->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'signed_by' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/finance/contracts/{$this->contract->id}/signatures/{$signature->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('contract_signatures', ['id' => $signature->id]);
    }
}
