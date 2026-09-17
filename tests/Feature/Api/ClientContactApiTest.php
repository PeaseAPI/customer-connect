<?php

namespace Tests\Feature\Api;

use App\Models\ClientContact;
use App\Models\ClientNote;
use App\Models\ClientDocument;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientContactApiTest extends TestCase
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

    public function test_can_list_client_contacts(): void
    {
        ClientContact::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
        ]);

        $response = $this->getJson("/api/crm/clients/{$this->client->id}/contacts");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_client_contact(): void
    {
        $response = $this->postJson("/api/crm/clients/{$this->client->id}/contacts", [
            'contact_name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('client_contacts', [
            'contact_name' => 'Jane Doe',
            'client_id' => $this->client->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_client_contact_without_name(): void
    {
        $response = $this->postJson("/api/crm/clients/{$this->client->id}/contacts", []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('contact_name');
    }

    public function test_can_show_client_contact(): void
    {
        $contact = ClientContact::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
        ]);

        $response = $this->getJson("/api/crm/clients/{$this->client->id}/contacts/{$contact->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_client_contact(): void
    {
        $contact = ClientContact::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
        ]);

        $response = $this->putJson("/api/crm/clients/{$this->client->id}/contacts/{$contact->id}", [
            'contact_name' => 'Updated Contact',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_client_contact(): void
    {
        $contact = ClientContact::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
        ]);

        $response = $this->deleteJson("/api/crm/clients/{$this->client->id}/contacts/{$contact->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('client_contacts', ['id' => $contact->id]);
    }
}
