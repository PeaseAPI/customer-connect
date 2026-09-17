<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Project;
use App\Models\Task;
use App\Models\Timelog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TimelogApiTest extends TestCase
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

    public function test_can_list_timelogs(): void
    {
        Timelog::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson('/api/pm/timelogs');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_timelog(): void
    {
        $response = $this->postJson('/api/pm/timelogs', [
            'user_id' => $this->adminUser->id,
            'start_time' => '2026-09-14 09:00:00',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('timelogs', [
            'user_id' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_timelog_without_required_fields(): void
    {
        $response = $this->postJson('/api/pm/timelogs', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'start_time']);
    }

    public function test_can_show_timelog(): void
    {
        $timelog = Timelog::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/pm/timelogs/{$timelog->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_timelog(): void
    {
        $timelog = Timelog::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->putJson("/api/pm/timelogs/{$timelog->id}", [
            'memo' => 'Updated memo',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_timelog(): void
    {
        $timelog = Timelog::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'task_id' => $this->task->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/pm/timelogs/{$timelog->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('timelogs', ['id' => $timelog->id]);
    }
}
