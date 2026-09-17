<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectApiTest extends TestCase
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

    public function test_can_list_projects(): void
    {
        Project::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/pm/projects');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_project(): void
    {
        $response = $this->postJson('/api/pm/projects', [
            'project_name' => 'Website Redesign',
            'start_date' => '2026-10-01',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('projects', [
            'project_name' => 'Website Redesign',
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_project_without_required_fields(): void
    {
        $response = $this->postJson('/api/pm/projects', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['project_name', 'start_date']);
    }

    public function test_can_create_project_with_all_fields(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/pm/projects', [
            'project_name' => 'ERP Development',
            'client_id' => $client->id,
            'start_date' => '2026-10-01',
            'deadline' => '2027-03-31',
            'status' => 'planning',
            'priority' => 'high',
            'budget' => 200000,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('projects', [
            'project_name' => 'ERP Development',
            'client_id' => $client->id,
            'status' => 'planning',
            'priority' => 'high',
            'budget' => 200000,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_project_with_invalid_status(): void
    {
        $response = $this->postJson('/api/pm/projects', [
            'project_name' => 'Test Project',
            'start_date' => '2026-10-01',
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_cannot_create_project_with_invalid_priority(): void
    {
        $response = $this->postJson('/api/pm/projects', [
            'project_name' => 'Test Project',
            'start_date' => '2026-10-01',
            'priority' => 'super_urgent',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('priority');
    }

    public function test_can_show_project(): void
    {
        $project = Project::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/pm/projects/{$project->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $project->id]]);
    }

    public function test_can_update_project(): void
    {
        $project = Project::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/pm/projects/{$project->id}", [
            'project_name' => 'Updated Project Name',
            'status' => 'in_progress',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'project_name' => 'Updated Project Name',
            'status' => 'in_progress',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_add_member_to_project(): void
    {
        $project = Project::factory()->create([
            'company_id' => $this->company->id,
        ]);
        $member = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson("/api/pm/projects/{$project->id}/members", [
            'user_id' => $member->id,
            'role' => 'member',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
            'role' => 'member',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_remove_member_from_project(): void
    {
        $project = Project::factory()->create([
            'company_id' => $this->company->id,
        ]);
        $member = User::factory()->create(['company_id' => $this->company->id]);
        $project->members()->attach($member->id, [
            'company_id' => $project->company_id,
            'role' => 'member',
        ]);

        $response = $this->deleteJson("/api/pm/projects/{$project->id}/members/{$member->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('project_members', [
            'project_id' => $project->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_cannot_access_projects_when_unauthenticated(): void
    {
        // Do not authenticate — clear the acting user
        auth()->forgetGuards();

        $response = $this->getJson('/api/pm/projects');

        $response->assertUnauthorized();
    }

    public function test_projects_are_scoped_to_company(): void
    {
        Project::factory()->create(['company_id' => $this->company->id]);

        $otherCompany = Company::factory()->create();
        $otherProject = Project::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $response = $this->getJson('/api/pm/projects');

        $response->assertOk();
        $projectIds = collect($response->json('data'))->pluck('id');
        $this->assertFalse($projectIds->contains($otherProject->id));
    }
}
