<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected User $clientUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->clientUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_invoices(): void
    {
        Invoice::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $this->clientUser->id,
        ]);

        $response = $this->getJson('/api/finance/invoices');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_invoice(): void
    {
        $response = $this->postJson('/api/finance/invoices', [
            'client_id' => $this->clientUser->id,
            'sub_total' => 1000,
            'total' => 1100,
            'date' => '2026-01-01',
            'due_date' => '2026-01-31',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('invoices', [
            'client_id' => $this->clientUser->id,
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_invoice_without_client_id(): void
    {
        $response = $this->postJson('/api/finance/invoices', [
            'sub_total' => 1000,
            'total' => 1100,
            'date' => '2026-01-01',
            'due_date' => '2026-01-31',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('client_id');
    }

    public function test_can_show_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->clientUser->id,
        ]);

        $response = $this->getJson("/api/finance/invoices/{$invoice->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_invoice_status(): void
    {
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->clientUser->id,
        ]);

        $response = $this->putJson("/api/finance/invoices/{$invoice->id}", [
            'status' => 'sent',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->clientUser->id,
        ]);

        $response = $this->deleteJson("/api/finance/invoices/{$invoice->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_send_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $this->clientUser->id,
            'status' => 'draft',
        ]);

        $response = $this->postJson("/api/finance/invoices/{$invoice->id}/send");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'sent',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_invoice_store_generates_invoice_number(): void
    {
        $response = $this->postJson('/api/finance/invoices', [
            'client_id' => $this->clientUser->id,
            'sub_total' => 1000,
            'total' => 1100,
            'date' => '2026-01-01',
            'due_date' => '2026-01-31',
        ]);

        $response->assertStatus(201);
        $invoice = Invoice::where('client_id', $this->clientUser->id)->first();
        $this->assertNotNull($invoice->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }
}
