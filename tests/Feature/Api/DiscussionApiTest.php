<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Discussion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DiscussionApiTest extends TestCase
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

    public function test_can_list_discussions(): void
    {
        Discussion::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/discussions');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_discussion(): void
    {
        $response = $this->postJson('/api/discussions', [
            'title' => 'New Feature Discussion',
            'description' => 'Let us discuss the upcoming feature changes',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('discussions', [
            'title' => 'New Feature Discussion',
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_discussion_without_title(): void
    {
        $response = $this->postJson('/api/discussions', [
            'description' => 'Missing title',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_can_show_discussion(): void
    {
        $discussion = Discussion::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/discussions/{$discussion->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $discussion->id]]);
    }

    public function test_can_update_discussion(): void
    {
        $discussion = Discussion::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/discussions/{$discussion->id}", [
            'title' => 'Updated Discussion Title',
            'is_pinned' => true,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('discussions', [
            'id' => $discussion->id,
            'title' => 'Updated Discussion Title',
            'is_pinned' => true,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_discussion(): void
    {
        $discussion = Discussion::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/discussions/{$discussion->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('discussions', ['id' => $discussion->id, 'company_id' => $this->company->id]);
    }

    public function test_can_list_discussion_replies(): void
    {
        $discussion = Discussion::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/discussions/{$discussion->id}/replies");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_add_reply_to_discussion(): void
    {
        $discussion = Discussion::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson("/api/discussions/{$discussion->id}/replies", [
            'body' => 'This is my reply to the discussion',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('discussion_replies', [
            'discussion_id' => $discussion->id,
            'user_id' => $this->adminUser->id,
            'body' => 'This is my reply to the discussion',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_add_reply_without_body(): void
    {
        $discussion = Discussion::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson("/api/discussions/{$discussion->id}/replies", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('body');
    }
}
