<?php

namespace Tests\Feature\Api;

use App\Models\ClientContact;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientApiTest extends TestCase
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
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_clients(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $client->assignRole('client');

        $response = $this->getJson('/api/crm/clients');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_client(): void
    {
        $response = $this->postJson('/api/crm/clients', [
            'name' => 'Test Client',
            'email' => 'client@test.com',
            'password' => 'password123',
            'company_name' => 'Client Corp',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('users', [
            'email' => 'client@test.com',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_client_without_required_fields(): void
    {
        $response = $this->postJson('/api/crm/clients', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_can_show_client(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $client->assignRole('client');

        $response = $this->getJson("/api/crm/clients/{$client->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_client(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $client->assignRole('client');

        $response = $this->putJson("/api/crm/clients/{$client->id}", [
            'name' => 'Updated Client',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_client(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $client->assignRole('client');

        $response = $this->deleteJson("/api/crm/clients/{$client->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
