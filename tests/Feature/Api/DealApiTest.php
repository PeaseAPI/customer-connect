<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Deal;
use App\Models\LeadPipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DealApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected LeadPipeline $pipeline;
    protected PipelineStage $stage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->pipeline = LeadPipeline::factory()->create(['company_id' => $this->company->id]);
        $this->stage = PipelineStage::factory()->create([
            'company_id' => $this->company->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'type' => 'lead',
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_deals(): void
    {
        Deal::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $response = $this->getJson('/api/crm/deals');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_deal(): void
    {
        $agent = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/crm/deals', [
            'deal_name' => 'Enterprise License',
            'pipeline_stage_id' => $this->stage->id,
            'agent_id' => $agent->id,
            'value' => 50000,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('deals', [
            'deal_name' => 'Enterprise License',
            'pipeline_stage_id' => $this->stage->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_deal_without_required_fields(): void
    {
        $response = $this->postJson('/api/crm/deals', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['deal_name', 'pipeline_stage_id']);
    }

    public function test_can_show_deal(): void
    {
        $deal = Deal::factory()->create([
            'company_id' => $this->company->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $response = $this->getJson("/api/crm/deals/{$deal->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $deal->id]]);
    }

    public function test_can_update_deal(): void
    {
        $deal = Deal::factory()->create([
            'company_id' => $this->company->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $response = $this->putJson("/api/crm/deals/{$deal->id}", [
            'deal_name' => 'Updated Deal Name',
            'value' => 75000,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'deal_name' => 'Updated Deal Name',
            'value' => 75000,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_change_deal_stage(): void
    {
        $deal = Deal::factory()->create([
            'company_id' => $this->company->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $newStage = PipelineStage::factory()->create([
            'company_id' => $this->company->id,
            'lead_pipeline_id' => $this->pipeline->id,
            'type' => 'won',
        ]);

        $response = $this->postJson("/api/crm/deals/{$deal->id}/change-stage", [
            'pipeline_stage_id' => $newStage->id,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('deals', [
            'id' => $deal->id,
            'pipeline_stage_id' => $newStage->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_deal(): void
    {
        $deal = Deal::factory()->create([
            'company_id' => $this->company->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $response = $this->deleteJson("/api/crm/deals/{$deal->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('deals', ['id' => $deal->id, 'company_id' => $this->company->id]);
    }

    public function test_can_add_note_to_deal(): void
    {
        $deal = Deal::factory()->create([
            'company_id' => $this->company->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $response = $this->postJson("/api/crm/deals/{$deal->id}/notes", [
            'note' => 'Follow up next week',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_can_view_deal_history(): void
    {
        $deal = Deal::factory()->create([
            'company_id' => $this->company->id,
            'pipeline_stage_id' => $this->stage->id,
        ]);

        $response = $this->getJson("/api/crm/deals/{$deal->id}/history");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
