<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProposalApiTest extends TestCase
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

    public function test_can_list_proposals(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        Proposal::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson('/api/crm/proposals');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_proposal(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/crm/proposals', [
            'client_id' => $client->id,
            'subject' => 'Website Redesign Proposal',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('proposals', [
            'client_id' => $client->id,
            'subject' => 'Website Redesign Proposal',
            'status' => 'draft',
            'company_id' => $this->company->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_can_show_proposal(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $proposal = Proposal::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson("/api/crm/proposals/{$proposal->id}");
        $response->assertOk();
                $response->assertJson(['success' => true, 'data' => ['id' => $proposal->id]]);
    }

    public function test_can_update_proposal(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $proposal = Proposal::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->putJson("/api/crm/proposals/{$proposal->id}", [
            'subject' => 'Updated Subject',
            'status' => 'sent',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_proposal(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $proposal = Proposal::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->deleteJson("/api/crm/proposals/{$proposal->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('proposals', ['id' => $proposal->id, 'company_id' => $this->company->id]);
    }

    public function test_can_send_proposal(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $proposal = Proposal::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'status' => 'draft',
        ]);

        $response = $this->postJson("/api/crm/proposals/{$proposal->id}/send");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('proposals', ['id' => $proposal->id, 'status' => 'sent', 'company_id' => $this->company->id]);
    }

    public function test_cannot_convert_non_accepted_proposal(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $proposal = Proposal::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'status' => 'draft',
        ]);

        $response = $this->postJson("/api/crm/proposals/{$proposal->id}/convert-to-invoice");
        $response->assertStatus(400);
    }

    public function test_can_convert_accepted_proposal_to_invoice(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $proposal = Proposal::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'status' => 'accepted',
            'sub_total' => 10000,
            'total' => 10000,
        ]);

        $response = $this->postJson("/api/crm/proposals/{$proposal->id}/convert-to-invoice");
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('invoices', [
            'client_id' => $client->id,
            'company_id' => $this->company->id,
            'sub_total' => 10000,
            'total' => 10000,
        ]);
    }
}

