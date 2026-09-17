<?php

namespace Tests\Feature\Api;

use App\Models\ClientNote;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientNoteApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->client = User::factory()->create(['company_id' => $this->company->id]);
        $this->client->assignRole('client');

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_client_notes(): void
    {
        ClientNote::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/crm/clients/{$this->client->id}/notes");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_client_note(): void
    {
        $response = $this->postJson("/api/crm/clients/{$this->client->id}/notes", [
            'title' => 'Important Note',
            'details' => 'This is an important note about the client',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('client_notes', [
            'title' => 'Important Note',
            'client_id' => $this->client->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_client_note_without_title(): void
    {
        $response = $this->postJson("/api/crm/clients/{$this->client->id}/notes", []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_can_show_client_note(): void
    {
        $note = ClientNote::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/crm/clients/{$this->client->id}/notes/{$note->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_client_note(): void
    {
        $note = ClientNote::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->putJson("/api/crm/clients/{$this->client->id}/notes/{$note->id}", [
            'title' => 'Updated Note',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_client_note(): void
    {
        $note = ClientNote::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/crm/clients/{$this->client->id}/notes/{$note->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('client_notes', ['id' => $note->id]);
    }
}
