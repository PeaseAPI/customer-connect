<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\EstimateRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EstimateRequestApiTest extends TestCase
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

    public function test_can_list_estimate_requests(): void
    {
        EstimateRequest::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/crm/estimate-requests');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_estimate_request(): void
    {
        $response = $this->postJson('/api/crm/estimate-requests', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
            'company_name' => 'Acme Corp',
            'requirement' => 'Need a website redesign',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('estimate_requests', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_estimate_request_without_required_fields(): void
    {
        $response = $this->postJson('/api/crm/estimate-requests', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'email']);
    }

    public function test_cannot_create_estimate_request_with_invalid_email(): void
    {
        $response = $this->postJson('/api/crm/estimate-requests', [
            'name' => 'John',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_cannot_create_estimate_request_with_invalid_status(): void
    {
        $response = $this->postJson('/api/crm/estimate-requests', [
            'name' => 'John',
            'email' => 'john@example.com',
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_can_show_estimate_request(): void
    {
        $estimate = EstimateRequest::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/crm/estimate-requests/{$estimate->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $estimate->id]]);
    }

    public function test_can_update_estimate_request(): void
    {
        $estimate = EstimateRequest::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/crm/estimate-requests/{$estimate->id}", [
            'name' => 'Jane',
            'email' => 'jane@example.com',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('estimate_requests', [
            'id' => $estimate->id,
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_estimate_request(): void
    {
        $estimate = EstimateRequest::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/crm/estimate-requests/{$estimate->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('estimate_requests', ['id' => $estimate->id]);
    }
}
