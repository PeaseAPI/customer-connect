<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EventApiTest extends TestCase
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

    public function test_can_list_events(): void
    {
        Event::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/events');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_event(): void
    {
        $response = $this->postJson('/api/events', [
            'event_name' => 'Team Building',
            'start_date_time' => '2026-10-01 09:00:00',
            'end_date_time' => '2026-10-01 17:00:00',
            'location' => 'Conference Room A',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('events', [
            'event_name' => 'Team Building',
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_event_without_name(): void
    {
        $response = $this->postJson('/api/events', [
            'start_date_time' => '2026-10-01 09:00:00',
            'end_date_time' => '2026-10-01 17:00:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('event_name');
    }

    public function test_cannot_create_event_without_start_date(): void
    {
        $response = $this->postJson('/api/events', [
            'event_name' => 'Team Building',
            'end_date_time' => '2026-10-01 17:00:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('start_date_time');
    }

    public function test_can_show_event(): void
    {
        $event = Event::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/events/{$event->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $event->id]]);
    }

    public function test_can_update_event(): void
    {
        $event = Event::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/events/{$event->id}", [
            'event_name' => 'Updated Event',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'event_name' => 'Updated Event',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_event(): void
    {
        $event = Event::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/events/{$event->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }
}
