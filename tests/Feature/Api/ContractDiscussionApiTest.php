<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Contract;
use App\Models\ContractDiscussion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractDiscussionApiTest extends TestCase
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

    public function test_can_list_contract_discussions(): void
    {
        ContractDiscussion::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/finance/contracts/{$this->contract->id}/discussions");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_contract_discussion(): void
    {
        $response = $this->postJson("/api/finance/contracts/{$this->contract->id}/discussions", [
            'comment' => 'This contract needs review',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('contract_discussions', [
            'comment' => 'This contract needs review',
            'contract_id' => $this->contract->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_discussion_without_comment(): void
    {
        $response = $this->postJson("/api/finance/contracts/{$this->contract->id}/discussions", []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('comment');
    }

    public function test_can_show_contract_discussion(): void
    {
        $discussion = ContractDiscussion::factory()->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/finance/contracts/{$this->contract->id}/discussions/{$discussion->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_contract_discussion(): void
    {
        $discussion = ContractDiscussion::factory()->create([
            'company_id' => $this->company->id,
            'contract_id' => $this->contract->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/finance/contracts/{$this->contract->id}/discussions/{$discussion->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('contract_discussions', ['id' => $discussion->id]);
    }
}
