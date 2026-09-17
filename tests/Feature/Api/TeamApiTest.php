<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeamApiTest extends TestCase
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

    public function test_can_list_teams(): void
    {
        Team::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/teams');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_team(): void
    {
        $response = $this->postJson('/api/teams', [
            'team_name' => 'Alpha Team',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('teams', [
            'team_name' => 'Alpha Team',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_team_without_name(): void
    {
        $response = $this->postJson('/api/teams', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('team_name');
    }

    public function test_cannot_create_duplicate_team_name(): void
    {
        Team::factory()->create(['company_id' => $this->company->id, 'team_name' => 'Alpha']);

        $response = $this->postJson('/api/teams', [
            'team_name' => 'Alpha',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('team_name');
    }

    public function test_can_create_team_with_members(): void
    {
        $member = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/teams', [
            'team_name' => 'Beta Team',
            'member_ids' => [$member->id],
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_can_show_team(): void
    {
        $team = Team::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/teams/{$team->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $team->id]]);
    }

    public function test_can_update_team(): void
    {
        $team = Team::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/teams/{$team->id}", [
            'team_name' => 'Updated Team',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'team_name' => 'Updated Team',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_team(): void
    {
        $team = Team::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/teams/{$team->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_can_add_members_to_team(): void
    {
        $team = Team::factory()->create(['company_id' => $this->company->id]);
        $member = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson("/api/teams/{$team->id}/add-members", [
            'member_ids' => [$member->id],
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_remove_member_from_team(): void
    {
        $team = Team::factory()->create(['company_id' => $this->company->id]);
        $member = User::factory()->create(['company_id' => $this->company->id]);
        $team->members()->attach($member, ['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/teams/{$team->id}/members/{$member->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
