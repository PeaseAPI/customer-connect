<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectMilestone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectMilestoneApiTest extends TestCase
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

    public function test_can_list_project_milestones(): void
    {
        ProjectMilestone::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson("/api/pm/projects/{$this->project->id}/milestones");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_project_milestone(): void
    {
        $response = $this->postJson("/api/pm/projects/{$this->project->id}/milestones", [
            'milestone_title' => 'Phase 1 - Design',
            'start_date' => '2026-10-01',
            'end_date' => '2026-12-31',
            'cost' => 50000,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('project_milestones', [
            'milestone_title' => 'Phase 1 - Design',
            'project_id' => $this->project->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_milestone_without_title(): void
    {
        $response = $this->postJson("/api/pm/projects/{$this->project->id}/milestones", [
            'start_date' => '2026-10-01',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('milestone_title');
    }

    public function test_can_show_project_milestone(): void
    {
        $milestone = ProjectMilestone::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson("/api/pm/projects/{$this->project->id}/milestones/{$milestone->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $milestone->id]]);
    }

    public function test_can_update_project_milestone(): void
    {
        $milestone = ProjectMilestone::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->putJson("/api/pm/projects/{$this->project->id}/milestones/{$milestone->id}", [
            'milestone_title' => 'Updated Phase',
            'cost' => 75000,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('project_milestones', [
            'id' => $milestone->id,
            'milestone_title' => 'Updated Phase',
            'cost' => 75000,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_project_milestone(): void
    {
        $milestone = ProjectMilestone::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->deleteJson("/api/pm/projects/{$this->project->id}/milestones/{$milestone->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('project_milestones', ['id' => $milestone->id]);
    }
}
