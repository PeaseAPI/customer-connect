<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MilestoneApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->project = Project::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_milestones(): void
    {
        Milestone::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson('/api/pm/milestones');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_milestone(): void
    {
        $response = $this->postJson('/api/pm/milestones', [
            'project_id' => $this->project->id,
            'milestone_title' => 'Phase 1',
            'start_date' => '2026-01-01',
            'deadline' => '2026-03-31',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('milestones', [
            'milestone_title' => 'Phase 1',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_milestone_without_required_fields(): void
    {
        $response = $this->postJson('/api/pm/milestones', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['project_id', 'milestone_title']);
    }

    public function test_can_show_milestone(): void
    {
        $milestone = Milestone::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson("/api/pm/milestones/{$milestone->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_milestone(): void
    {
        $milestone = Milestone::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->putJson("/api/pm/milestones/{$milestone->id}", [
            'milestone_title' => 'Updated Milestone',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_milestone(): void
    {
        $milestone = Milestone::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->deleteJson("/api/pm/milestones/{$milestone->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('milestones', ['id' => $milestone->id]);
    }
}
