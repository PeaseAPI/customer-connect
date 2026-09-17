<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\LeadPipeline;
use App\Models\PipelineStage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PipelineApiTest extends TestCase
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

    public function test_can_list_pipelines(): void
    {
        LeadPipeline::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/crm/pipelines');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_pipeline(): void
    {
        $response = $this->postJson('/api/crm/pipelines', [
            'name' => 'Sales Pipeline',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('lead_pipelines', [
            'name' => 'Sales Pipeline',
            'company_id' => $this->company->id,
        ]);

        // Pipeline should create default stages
        $pipeline = LeadPipeline::where('name', 'Sales Pipeline')->first();
        $this->assertGreaterThan(0, $pipeline->stages()->count());
    }

    public function test_cannot_create_pipeline_without_name(): void
    {
        $response = $this->postJson('/api/crm/pipelines', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_can_show_pipeline(): void
    {
        $pipeline = LeadPipeline::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/crm/pipelines/{$pipeline->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $pipeline->id]]);
    }

    public function test_can_update_pipeline(): void
    {
        $pipeline = LeadPipeline::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/crm/pipelines/{$pipeline->id}", [
            'name' => 'Updated Pipeline Name',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('lead_pipelines', [
            'id' => $pipeline->id,
            'name' => 'Updated Pipeline Name',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_pipeline(): void
    {
        $pipeline = LeadPipeline::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/crm/pipelines/{$pipeline->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('lead_pipelines', ['id' => $pipeline->id]);
    }

    public function test_can_add_stage_to_pipeline(): void
    {
        $pipeline = LeadPipeline::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson("/api/crm/pipelines/{$pipeline->id}/stages", [
            'name' => 'Negotiation',
            'type' => 'lead',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('pipeline_stages', [
            'name' => 'Negotiation',
            'lead_pipeline_id' => $pipeline->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_update_pipeline_stage(): void
    {
        $pipeline = LeadPipeline::factory()->create(['company_id' => $this->company->id]);
        $stage = PipelineStage::factory()->create([
            'company_id' => $this->company->id,
            'lead_pipeline_id' => $pipeline->id,
        ]);

        $response = $this->putJson("/api/crm/pipeline-stages/{$stage->id}", [
            'name' => 'Updated Stage Name',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('pipeline_stages', [
            'id' => $stage->id,
            'name' => 'Updated Stage Name',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_pipeline_stage(): void
    {
        $pipeline = LeadPipeline::factory()->create(['company_id' => $this->company->id]);
        $stage = PipelineStage::factory()->create([
            'company_id' => $this->company->id,
            'lead_pipeline_id' => $pipeline->id,
        ]);

        $response = $this->deleteJson("/api/crm/pipeline-stages/{$stage->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('pipeline_stages', ['id' => $stage->id]);
    }
}
