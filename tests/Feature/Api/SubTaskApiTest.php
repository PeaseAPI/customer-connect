<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Project;
use App\Models\SubTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SubTaskApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected Project $project;
    protected Task $task;

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

        $this->task = Task::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'created_by' => $this->adminUser->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_sub_tasks(): void
    {
        SubTask::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'task_id' => $this->task->id,
        ]);

        $response = $this->getJson("/api/pm/tasks/{$this->task->id}/sub-tasks");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_sub_task(): void
    {
        $response = $this->postJson("/api/pm/tasks/{$this->task->id}/sub-tasks", [
            'title' => 'Design mockups',
            'description' => 'Create UI mockups for the new feature',
            'due_date' => '2026-10-15',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('sub_tasks', [
            'title' => 'Design mockups',
            'task_id' => $this->task->id,
            'company_id' => $this->company->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_sub_task_without_title(): void
    {
        $response = $this->postJson("/api/pm/tasks/{$this->task->id}/sub-tasks", [
            'description' => 'No title subtask',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_can_update_sub_task(): void
    {
        $subTask = SubTask::factory()->create([
            'company_id' => $this->company->id,
            'task_id' => $this->task->id,
        ]);

        $response = $this->putJson("/api/pm/sub-tasks/{$subTask->id}", [
            'title' => 'Updated title',
            'status' => 'complete',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('sub_tasks', [
            'id' => $subTask->id,
            'title' => 'Updated title',
            'status' => 'complete',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_update_sub_task_with_invalid_status(): void
    {
        $subTask = SubTask::factory()->create([
            'company_id' => $this->company->id,
            'task_id' => $this->task->id,
        ]);

        $response = $this->putJson("/api/pm/sub-tasks/{$subTask->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_can_delete_sub_task(): void
    {
        $subTask = SubTask::factory()->create([
            'company_id' => $this->company->id,
            'task_id' => $this->task->id,
        ]);

        $response = $this->deleteJson("/api/pm/sub-tasks/{$subTask->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('sub_tasks', ['id' => $subTask->id]);
    }
}
