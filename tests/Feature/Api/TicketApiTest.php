<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketApiTest extends TestCase
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

    public function test_can_list_tickets(): void
    {
        Ticket::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/tickets');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_ticket(): void
    {
        $response = $this->postJson('/api/tickets', [
            'subject' => 'Cannot login',
            'description' => 'I cannot login to the system',
            'priority' => 'high',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tickets', [
            'subject' => 'Cannot login',
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_ticket_without_subject(): void
    {
        $response = $this->postJson('/api/tickets', [
            'description' => 'Some description',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('subject');
    }

    public function test_can_show_ticket(): void
    {
        $ticket = Ticket::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/tickets/{$ticket->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $ticket->id]]);
    }

    public function test_can_update_ticket_status(): void
    {
        $ticket = Ticket::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/tickets/{$ticket->id}", [
            'status' => 'resolved',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_ticket(): void
    {
        $ticket = Ticket::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/tickets/{$ticket->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('tickets', ['id' => $ticket->id, 'company_id' => $this->company->id]);
    }

    public function test_can_filter_tickets_by_status(): void
    {
        Ticket::factory()->create(['company_id' => $this->company->id, 'status' => 'open']);
        Ticket::factory()->create(['company_id' => $this->company->id, 'status' => 'closed']);

        $response = $this->getJson('/api/tickets?status=open');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
