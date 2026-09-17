<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectTimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectTimeLogApiTest extends TestCase
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

    public function test_can_list_project_time_logs(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        ProjectTimeLog::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("/api/pm/projects/{$this->project->id}/time-logs");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_project_time_log(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson("/api/pm/projects/{$this->project->id}/time-logs", [
            'user_id' => $user->id,
            'log_date' => '2026-09-15',
            'start_time' => '09:00',
            'end_time' => '17:00',
            'total_hours' => 8.0,
            'note' => 'Working on API module',
            'billable' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('project_time_logs', [
            'project_id' => $this->project->id,
            'user_id' => $user->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_time_log_without_user_id(): void
    {
        $response = $this->postJson("/api/pm/projects/{$this->project->id}/time-logs", [
            'log_date' => '2026-09-15',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }

    public function test_cannot_create_time_log_without_log_date(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson("/api/pm/projects/{$this->project->id}/time-logs", [
            'user_id' => $user->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('log_date');
    }

    public function test_can_show_project_time_log(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $timeLog = ProjectTimeLog::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("/api/pm/projects/{$this->project->id}/time-logs/{$timeLog->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $timeLog->id]]);
    }

    public function test_can_update_project_time_log(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $timeLog = ProjectTimeLog::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'user_id' => $user->id,
        ]);

        $response = $this->putJson("/api/pm/projects/{$this->project->id}/time-logs/{$timeLog->id}", [
            'note' => 'Updated note',
            'billable' => false,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('project_time_logs', [
            'id' => $timeLog->id,
            'note' => 'Updated note',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_project_time_log(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $timeLog = ProjectTimeLog::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson("/api/pm/projects/{$this->project->id}/time-logs/{$timeLog->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('project_time_logs', ['id' => $timeLog->id]);
    }

    public function test_can_start_break_on_time_log(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $timeLog = ProjectTimeLog::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'user_id' => $user->id,
        ]);

        $response = $this->postJson("/api/pm/projects/{$this->project->id}/time-logs/{$timeLog->id}/start-break");
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('project_time_log_breaks', [
            'project_time_log_id' => $timeLog->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_end_break_on_time_log(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $timeLog = ProjectTimeLog::factory()->create([
            'company_id' => $this->company->id,
            'project_id' => $this->project->id,
            'user_id' => $user->id,
        ]);
        $breakLog = $timeLog->breaks()->create([
            'company_id' => $this->company->id,
            'break_start' => now()->subMinutes(30)->format('H:i'),
        ]);

        $response = $this->postJson("/api/pm/projects/{$this->project->id}/time-logs/{$timeLog->id}/breaks/{$breakLog->id}/end");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertNotNull($breakLog->fresh()->break_end);
    }
}
