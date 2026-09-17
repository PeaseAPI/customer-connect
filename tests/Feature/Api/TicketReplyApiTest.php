<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TicketReplyApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->ticket = Ticket::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_ticket_replies(): void
    {
        TicketReply::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/tickets/{$this->ticket->id}/replies");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_ticket_reply(): void
    {
        $response = $this->postJson("/api/tickets/{$this->ticket->id}/replies", [
            'message' => 'This is a reply to the ticket',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('ticket_replies', [
            'message' => 'This is a reply to the ticket',
            'ticket_id' => $this->ticket->id,
            'user_id' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_reply_without_message(): void
    {
        $response = $this->postJson("/api/tickets/{$this->ticket->id}/replies", []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('message');
    }
}
