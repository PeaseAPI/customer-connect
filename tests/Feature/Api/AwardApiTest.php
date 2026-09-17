<?php

namespace Tests\Feature\Api;

use App\Models\Award;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AwardApiTest extends TestCase
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

    public function test_can_list_awards(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        Award::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson('/api/hrm/awards');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_award(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/hrm/awards', [
            'user_id' => $user->id,
            'title' => 'Best Performer',
            'award_date' => '2026-01-15',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('awards', [
            'title' => 'Best Performer',
            'user_id' => $user->id,
            'company_id' => $this->company->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_award_without_required_fields(): void
    {
        $response = $this->postJson('/api/hrm/awards', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['user_id', 'title', 'award_date']);
    }

    public function test_cannot_create_award_with_invalid_user(): void
    {
        $response = $this->postJson('/api/hrm/awards', [
            'user_id' => 99999,
            'title' => 'Test Award',
            'award_date' => '2026-01-15',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('user_id');
    }

    public function test_can_show_award(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $award = Award::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
        ]);

        $response = $this->getJson("/api/hrm/awards/{$award->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $award->id]]);
    }

    public function test_can_update_award(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $award = Award::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
        ]);

        $response = $this->putJson("/api/hrm/awards/{$award->id}", [
            'title' => 'Updated Award Title',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('awards', [
            'id' => $award->id,
            'title' => 'Updated Award Title',
            'last_updated_by' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_award(): void
    {
        $user = User::factory()->create(['company_id' => $this->company->id]);
        $award = Award::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson("/api/hrm/awards/{$award->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('awards', ['id' => $award->id, 'company_id' => $this->company->id]);
    }
}
