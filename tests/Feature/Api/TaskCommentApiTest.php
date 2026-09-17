<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaskCommentApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
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

        $project = Project::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);

        $this->task = Task::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $project->id,
            'created_by' => $this->adminUser->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_task_comments(): void
    {
        TaskComment::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/pm/tasks/{$this->task->id}/comments");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_task_comment(): void
    {
        $response = $this->postJson("/api/pm/tasks/{$this->task->id}/comments", [
            'comment' => 'This task needs more investigation',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('task_comments', [
            'comment' => 'This task needs more investigation',
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_comment_without_body(): void
    {
        $response = $this->postJson("/api/pm/tasks/{$this->task->id}/comments", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('comment');
    }

    public function test_can_show_task_comment(): void
    {
        $comment = TaskComment::factory()->create([
            'company_id' => $this->company->id,
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
        ]);

                $response = $this->getJson("/api/pm/tasks/{$this->task->id}/comments/{$comment->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_task_comment(): void
    {
        $comment = TaskComment::factory()->create([
            'company_id' => $this->company->id,
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->putJson("/api/pm/tasks/{$this->task->id}/comments/{$comment->id}", [
            'comment' => 'Updated comment text',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_task_comment(): void
    {
        $comment = TaskComment::factory()->create([
            'company_id' => $this->company->id,
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/pm/tasks/{$this->task->id}/comments/{$comment->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('task_comments', ['id' => $comment->id]);
    }
}
