<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\OfflinePaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OfflinePaymentMethodApiTest extends TestCase
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

    public function test_can_list_offline_payment_methods(): void
    {
        OfflinePaymentMethod::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/finance/offline-payment-methods');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_offline_payment_method(): void
    {
        $response = $this->postJson('/api/finance/offline-payment-methods', [
            'method_name' => 'Bank Transfer',
            'bank_name' => 'ICBC',
            'bank_account_number' => '6222 0000 0000 0000',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('offline_payment_methods', [
            'method_name' => 'Bank Transfer',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_offline_payment_method_without_name(): void
    {
        $response = $this->postJson('/api/finance/offline-payment-methods', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('method_name');
    }

    public function test_can_show_offline_payment_method(): void
    {
        $method = OfflinePaymentMethod::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/finance/offline-payment-methods/{$method->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $method->id]]);
    }

    public function test_can_update_offline_payment_method(): void
    {
        $method = OfflinePaymentMethod::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/finance/offline-payment-methods/{$method->id}", [
            'method_name' => 'Wire Transfer',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('offline_payment_methods', [
            'id' => $method->id,
            'method_name' => 'Wire Transfer',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_offline_payment_method(): void
    {
        $method = OfflinePaymentMethod::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/finance/offline-payment-methods/{$method->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('offline_payment_methods', ['id' => $method->id]);
    }
}
