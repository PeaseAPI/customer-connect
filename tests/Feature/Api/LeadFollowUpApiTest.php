<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Lead;
use App\Models\LeadFollowUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeadFollowUpApiTest extends TestCase
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

    public function test_can_list_lead_follow_ups(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);
        LeadFollowUp::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'lead_id' => $lead->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/crm/leads/{$lead->id}/follow-ups");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

        public function test_can_create_lead_follow_up(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);

        $response = $this->postJson("/api/crm/leads/{$lead->id}/follow-ups", [
            'follow_up_date' => '2026-10-01',
            'remark' => 'Follow up on proposal',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('lead_follow_ups', [
            'lead_id' => $lead->id,
            'remark' => 'Follow up on proposal',
            'company_id' => $this->company->id,
        ]);
    }

        public function test_cannot_create_follow_up_without_required_fields(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);

        $response = $this->postJson("/api/crm/leads/{$lead->id}/follow-ups", []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['follow_up_date']);
    }

    public function test_can_update_lead_follow_up(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);
        $followUp = LeadFollowUp::factory()->create([
            'company_id' => $this->company->id,
            'lead_id' => $lead->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->putJson("/api/crm/leads/{$lead->id}/follow-ups/{$followUp->id}", [
            'remark' => 'Updated remark',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_lead_follow_up(): void
    {
        $lead = Lead::factory()->create(['company_id' => $this->company->id, 'created_by' => $this->adminUser->id]);
        $followUp = LeadFollowUp::factory()->create([
            'company_id' => $this->company->id,
            'lead_id' => $lead->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/crm/leads/{$lead->id}/follow-ups/{$followUp->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('lead_follow_ups', ['id' => $followUp->id]);
    }
}
