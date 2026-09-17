<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Tax;
use App\Models\UnitType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaxApiTest extends TestCase
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

        // Create admin role and assign
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_taxes(): void
    {
        Tax::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/finance/taxes');

        $response->assertOk();
        $response->assertJsonStructure([
            'success', 'message', 'data', 'meta' => [
                'current_page', 'last_page', 'per_page', 'total',
            ],
        ]);
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_tax(): void
    {
        $response = $this->postJson('/api/finance/taxes', [
            'tax_name' => 'GST',
            'tax_percent' => 10,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('taxes', [
            'tax_name' => 'GST',
            'tax_percent' => 10,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_tax_without_required_fields(): void
    {
        $response = $this->postJson('/api/finance/taxes', []);

        $this->assertValidationError($response, 'tax_name');
        $this->assertValidationError($response, 'tax_percent');
    }

    public function test_cannot_create_tax_with_percent_over_100(): void
    {
        $response = $this->postJson('/api/finance/taxes', [
            'tax_name' => 'Invalid Tax',
            'tax_percent' => 150,
        ]);

        $this->assertValidationError($response, 'tax_percent');
    }

    public function test_can_show_tax(): void
    {
        $tax = Tax::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/finance/taxes/{$tax->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $tax->id]]);
    }

    public function test_can_update_tax(): void
    {
        $tax = Tax::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/finance/taxes/{$tax->id}", [
            'tax_name' => 'VAT',
            'tax_percent' => 20,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('taxes', [
            'id' => $tax->id,
            'tax_name' => 'VAT',
            'tax_percent' => 20,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_tax(): void
    {
        $tax = Tax::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/finance/taxes/{$tax->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('taxes', ['id' => $tax->id]);
    }

    protected function assertValidationError($response, string $field): void
    {
        $response->assertStatus(422);
        $response->assertJsonValidationErrors($field);
    }
}
