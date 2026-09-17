<?php

namespace Tests\Feature\Api;

use App\Models\ClientDocument;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientDocumentApiTest extends TestCase
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

    public function test_can_list_client_documents(): void
    {
        ClientDocument::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/crm/clients/{$this->client->id}/documents");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_client_document(): void
    {
        $response = $this->postJson("/api/crm/clients/{$this->client->id}/documents", [
            'document_name' => 'Contract.pdf',
            'document_type' => 'pdf',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('client_documents', [
            'document_name' => 'Contract.pdf',
            'client_id' => $this->client->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_document_without_name(): void
    {
        $response = $this->postJson("/api/crm/clients/{$this->client->id}/documents", []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('document_name');
    }

    public function test_can_show_client_document(): void
    {
        $document = ClientDocument::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/crm/clients/{$this->client->id}/documents/{$document->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_client_document(): void
    {
        $document = ClientDocument::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->putJson("/api/crm/clients/{$this->client->id}/documents/{$document->id}", [
            'document_name' => 'Updated.pdf',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_client_document(): void
    {
        $document = ClientDocument::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->client->id,
            'added_by' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/crm/clients/{$this->client->id}/documents/{$document->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('client_documents', ['id' => $document->id]);
    }
}
