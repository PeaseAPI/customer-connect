<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadApiTest extends TestCase
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

    public function test_can_list_leads(): void
    {
        Lead::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/crm/leads');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_lead(): void
    {
        $response = $this->postJson('/api/crm/leads', [
            'lead_name' => 'Zhang Wei',
            'lead_email' => 'zhangwei@example.com',
            'lead_mobile' => '13800138000',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('leads', [
            'lead_name' => 'Zhang Wei',
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_lead_without_name(): void
    {
        $response = $this->postJson('/api/crm/leads', [
            'lead_email' => 'test@example.com',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('lead_name');
    }

    public function test_can_show_lead(): void
    {
        $lead = Lead::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/crm/leads/{$lead->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $lead->id]]);
    }

    public function test_can_update_lead(): void
    {
        $lead = Lead::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/crm/leads/{$lead->id}", [
            'lead_name' => 'Updated Name',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'lead_name' => 'Updated Name',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_lead(): void
    {
        $lead = Lead::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/crm/leads/{$lead->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('leads', ['id' => $lead->id, 'company_id' => $this->company->id]);
    }

        public function test_can_convert_lead_to_client(): void
    {
        $lead = Lead::factory()->create([
            'company_id' => $this->company->id,
            'lead_name' => 'Li Ming',
            'lead_email' => 'liming@example.com',
        ]);

        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);

        $response = $this->postJson("/api/crm/leads/{$lead->id}/convert");
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_can_search_leads(): void
    {
        Lead::factory()->create(['company_id' => $this->company->id, 'lead_name' => 'Zhang Wei']);
        Lead::factory()->create(['company_id' => $this->company->id, 'lead_name' => 'Li Ming']);

        $response = $this->getJson('/api/crm/leads?search=Zhang');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
