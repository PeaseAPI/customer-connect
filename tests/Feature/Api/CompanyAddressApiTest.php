<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyAddressApiTest extends TestCase
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

    public function test_can_list_company_addresses(): void
    {
        CompanyAddress::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/company-addresses');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_company_address(): void
    {
        $response = $this->postJson('/api/company-addresses', [
            'address' => '123 Business Street, Shanghai',
            'is_default' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('company_addresses', [
            'address' => '123 Business Street, Shanghai',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_company_address_without_address(): void
    {
        $response = $this->postJson('/api/company-addresses', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('address');
    }

    public function test_can_show_company_address(): void
    {
        $address = CompanyAddress::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/company-addresses/{$address->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $address->id]]);
    }

    public function test_can_update_company_address(): void
    {
        $address = CompanyAddress::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/company-addresses/{$address->id}", [
            'address' => '456 New Address, Beijing',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('company_addresses', [
            'id' => $address->id,
            'address' => '456 New Address, Beijing',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_company_address(): void
    {
        $address = CompanyAddress::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/company-addresses/{$address->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('company_addresses', ['id' => $address->id]);
    }

    public function test_setting_default_resets_other_defaults(): void
    {
        $existing = CompanyAddress::factory()->create([
            'company_id' => $this->company->id,
            'is_default' => true,
        ]);

        $response = $this->postJson('/api/company-addresses', [
            'address' => 'New Default Address',
            'is_default' => true,
        ]);

        $response->assertStatus(201);
                $this->assertDatabaseHas('company_addresses', [
            'id' => $existing->id,
            'is_default' => false,
            'company_id' => $this->company->id,
        ]);
    }
}
