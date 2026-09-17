<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\StickyNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StickyNoteApiTest extends TestCase
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

    public function test_can_list_sticky_notes(): void
    {
        StickyNote::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson('/api/sticky-notes');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_sticky_note(): void
    {
        $response = $this->postJson('/api/sticky-notes', [
            'note_text' => 'Remember to update the documentation',
            'color' => '#FFC107',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('sticky_notes', [
            'note_text' => 'Remember to update the documentation',
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_sticky_note_without_text(): void
    {
        $response = $this->postJson('/api/sticky-notes', [
            'color' => '#FFC107',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('note_text');
    }

    public function test_can_show_sticky_note(): void
    {
        $note = StickyNote::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/sticky-notes/{$note->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $note->id]]);
    }

    public function test_can_update_sticky_note(): void
    {
        $note = StickyNote::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->putJson("/api/sticky-notes/{$note->id}", [
            'note_text' => 'Updated note text',
            'color' => '#4CAF50',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_sticky_note(): void
    {
        $note = StickyNote::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/sticky-notes/{$note->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('sticky_notes', ['id' => $note->id]);
    }
}
