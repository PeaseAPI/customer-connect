<?php

namespace Tests\Feature\Api;

use App\Models\Chat;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChatApiTest extends TestCase
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

    public function test_can_list_chats(): void
    {
        Chat::factory()->count(3)->create(['company_id' => $this->company->id]);
        $response = $this->getJson('/api/chats');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_private_chat(): void
    {
        $otherUser = User::factory()->create(['company_id' => $this->company->id]);
        $response = $this->postJson('/api/chats', [
            'type' => 'private',
            'participant_ids' => [$otherUser->id],
        ]);
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_group_chat(): void
    {
        $user1 = User::factory()->create(['company_id' => $this->company->id]);
        $user2 = User::factory()->create(['company_id' => $this->company->id]);
        $response = $this->postJson('/api/chats', [
            'type' => 'group',
            'name' => 'Project Discussion',
            'participant_ids' => [$user1->id, $user2->id],
        ]);
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_cannot_create_chat_without_required_fields(): void
    {
        $response = $this->postJson('/api/chats', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['type', 'participant_ids']);
    }

    public function test_cannot_create_chat_with_invalid_type(): void
    {
        $otherUser = User::factory()->create(['company_id' => $this->company->id]);
        $response = $this->postJson('/api/chats', [
            'type' => 'invalid',
            'participant_ids' => [$otherUser->id],
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('type');
    }

    public function test_can_show_chat(): void
    {
        $chat = Chat::factory()->create(['company_id' => $this->company->id]);
        $response = $this->getJson("/api/chats/{$chat->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $chat->id]]);
    }

    public function test_can_update_chat(): void
    {
        $chat = Chat::factory()->create(['company_id' => $this->company->id, 'type' => 'group']);
        $response = $this->putJson("/api/chats/{$chat->id}", ['name' => 'Updated Chat Name']);
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_chat(): void
    {
        $chat = Chat::factory()->create(['company_id' => $this->company->id]);
        $response = $this->deleteJson("/api/chats/{$chat->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('chats', ['id' => $chat->id]);
    }

    public function test_can_get_chat_messages(): void
    {
        $chat = Chat::factory()->create(['company_id' => $this->company->id]);
        $response = $this->getJson("/api/chats/{$chat->id}/messages");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_send_message_to_chat(): void
    {
        $chat = Chat::factory()->create(['company_id' => $this->company->id]);
        $response = $this->postJson("/api/chats/{$chat->id}/messages", [
            'message' => 'Hello World!',
        ]);
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('chat_messages', [
            'chat_id' => $chat->id, 'message' => 'Hello World!',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_send_empty_message(): void
    {
        $chat = Chat::factory()->create(['company_id' => $this->company->id]);
        $response = $this->postJson("/api/chats/{$chat->id}/messages", ['message' => '']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }

    public function test_can_add_participants_to_chat(): void
    {
        $chat = Chat::factory()->create(['company_id' => $this->company->id, 'type' => 'group']);
        $newParticipant = User::factory()->create(['company_id' => $this->company->id]);
        $response = $this->postJson("/api/chats/{$chat->id}/add-participants", [
            'participant_ids' => [$newParticipant->id],
        ]);
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_remove_participant_from_chat(): void
    {
        $chat = Chat::factory()->create(['company_id' => $this->company->id, 'type' => 'group']);
        $participant = User::factory()->create(['company_id' => $this->company->id]);
        $chat->participants()->attach($participant);
        $response = $this->deleteJson("/api/chats/{$chat->id}/participants/{$participant->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
