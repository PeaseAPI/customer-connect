<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\RecurringInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RecurringInvoiceApiTest extends TestCase
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

    public function test_can_list_recurring_invoices(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        RecurringInvoice::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson('/api/finance/recurring-invoices');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_recurring_invoice(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/finance/recurring-invoices', [
            'client_id' => $client->id,
            'invoice_number' => 'RI-001',
            'frequency' => 'monthly',
            'start_date' => '2026-10-01',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('recurring_invoices', [
            'invoice_number' => 'RI-001',
            'client_id' => $client->id,
            'company_id' => $this->company->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_recurring_invoice_without_required_fields(): void
    {
        $response = $this->postJson('/api/finance/recurring-invoices', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_id', 'invoice_number', 'frequency', 'start_date']);
    }

    public function test_cannot_create_recurring_invoice_with_invalid_frequency(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/finance/recurring-invoices', [
            'client_id' => $client->id,
            'invoice_number' => 'RI-002',
            'frequency' => 'hourly',
            'start_date' => '2026-10-01',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('frequency');
    }

    public function test_can_show_recurring_invoice(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $invoice = RecurringInvoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson("/api/finance/recurring-invoices/{$invoice->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $invoice->id]]);
    }

    public function test_can_update_recurring_invoice(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $invoice = RecurringInvoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->putJson("/api/finance/recurring-invoices/{$invoice->id}", [
            'frequency' => 'weekly',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('recurring_invoices', [
            'id' => $invoice->id,
            'frequency' => 'weekly',
            'last_updated_by' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_recurring_invoice(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $invoice = RecurringInvoice::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->deleteJson("/api/finance/recurring-invoices/{$invoice->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('recurring_invoices', ['id' => $invoice->id, 'company_id' => $this->company->id]);
    }
}
