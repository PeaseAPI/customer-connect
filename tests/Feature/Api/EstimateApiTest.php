<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Estimate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EstimateApiTest extends TestCase
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

    public function test_can_list_estimates(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        Estimate::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson('/api/finance/estimates');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_estimate(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/finance/estimates', [
            'client_id' => $client->id,
            'sub_total' => 10000,
            'total' => 10000,
            'valid_till' => '2026-12-31',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('estimates', [
            'client_id' => $client->id,
            'sub_total' => 10000,
            'total' => 10000,
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_estimate_without_required_fields(): void
    {
        $response = $this->postJson('/api/finance/estimates', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_id', 'sub_total', 'total', 'valid_till']);
    }

    public function test_can_show_estimate(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $estimate = Estimate::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson("/api/finance/estimates/{$estimate->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_estimate_status(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $estimate = Estimate::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->putJson("/api/finance/estimates/{$estimate->id}", [
            'status' => 'accepted',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_cannot_update_estimate_with_invalid_status(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $estimate = Estimate::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->putJson("/api/finance/estimates/{$estimate->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_can_delete_estimate(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $estimate = Estimate::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->deleteJson("/api/finance/estimates/{$estimate->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
