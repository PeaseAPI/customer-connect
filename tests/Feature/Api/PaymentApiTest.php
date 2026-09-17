<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentApiTest extends TestCase
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

    public function test_can_list_payments(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        Payment::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson('/api/finance/payments');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_list_payments_for_invoice(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);
        Payment::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
            'invoice_id' => $invoice->id,
        ]);

        $response = $this->getJson("/api/finance/invoices/{$invoice->id}/payments");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_payment_for_invoice(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->postJson("/api/finance/invoices/{$invoice->id}/payments", [
            'client_id' => $client->id,
            'amount' => 5000,
            'gateway' => 'stripe',
            'paid_on' => '2026-09-14',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('payments', [
            'client_id' => $client->id,
            'invoice_id' => $invoice->id,
            'amount' => 5000,
            'gateway' => 'stripe',
            'created_by' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_payment_without_required_fields(): void
    {
        $response = $this->postJson('/api/finance/invoices/1/payments', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_id', 'amount', 'gateway', 'paid_on']);
    }

    public function test_cannot_create_payment_with_invalid_gateway(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->postJson("/api/finance/invoices/{$invoice->id}/payments", [
            'client_id' => $client->id,
            'amount' => 100,
            'gateway' => 'invalid_gateway',
            'paid_on' => '2026-09-14',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('gateway');
    }

    public function test_can_show_payment(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $payment = Payment::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson("/api/finance/payments/{$payment->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $payment->id]]);
    }
}
