<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadContactApiTest extends TestCase
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

    public function test_can_list_lead_contacts(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);
        LeadContact::factory()->count(3)->create(['lead_id' => $lead->id, 'company_id' => $this->company->id]);

        $response = $this->getJson('/api/crm/contacts');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_lead_contact(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);

        $response = $this->postJson('/api/crm/contacts', [
            'lead_id' => $lead->id,
            'contact_name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('lead_contacts', [
            'contact_name' => 'John Doe',
            'lead_id' => $lead->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_lead_contact_without_required_fields(): void
    {
        $response = $this->postJson('/api/crm/contacts', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['lead_id', 'contact_name']);
    }

    public function test_can_show_lead_contact(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);
        $contact = LeadContact::factory()->create(['lead_id' => $lead->id]);

        $response = $this->getJson("/api/crm/contacts/{$contact->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_lead_contact(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);
        $contact = LeadContact::factory()->create(['lead_id' => $lead->id]);

        $response = $this->putJson("/api/crm/contacts/{$contact->id}", [
            'contact_name' => 'Updated Contact',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_lead_contact(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);
        $contact = LeadContact::factory()->create(['lead_id' => $lead->id]);

        $response = $this->deleteJson("/api/crm/contacts/{$contact->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('lead_contacts', ['id' => $contact->id]);
    }
}
