<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaskApiTest extends TestCase
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

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);

        $this->project = Project::factory()->create([
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_list_tasks(): void
    {
        Task::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson("/api/pm/projects/{$this->project->id}/tasks");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_task(): void
    {
        $response = $this->postJson("/api/pm/projects/{$this->project->id}/tasks", [
            'title' => 'Implement authentication',
            'description' => 'Add Sanctum auth to the API',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tasks', [
            'title' => 'Implement authentication',
            'project_id' => $this->project->id,
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_task_without_title(): void
    {
        $response = $this->postJson("/api/pm/projects/{$this->project->id}/tasks", [
            'description' => 'Some task',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_can_show_task(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->getJson("/api/pm/projects/{$this->project->id}/tasks/{$task->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $task->id]]);
    }

    public function test_can_update_task(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->putJson("/api/pm/projects/{$this->project->id}/tasks/{$task->id}", [
            'title' => 'Updated Task Title',
            'status' => 'in_progress',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'company_id' => $this->company->id,
        ]);
    }

        public function test_can_delete_task(): void
    {
        $task = Task::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
        ]);

        $response = $this->deleteJson("/api/pm/projects/{$this->project->id}/tasks/{$task->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
