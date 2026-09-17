<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\InvoiceSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoiceSettingApiTest extends TestCase
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

    public function test_can_show_invoice_settings(): void
    {
        InvoiceSetting::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/finance/invoice-settings');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_invoice_settings(): void
    {
        InvoiceSetting::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson('/api/finance/invoice-settings', [
            'invoice_prefix' => 'INV-2026',
            'invoice_digits' => 5,
            'due_after' => '45',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('invoice_settings', [
            'company_id' => $this->company->id,
            'invoice_prefix' => 'INV-2026',
            'invoice_digits' => 5,
        ]);
    }

    public function test_cannot_update_invoice_settings_with_invalid_digits(): void
    {
        InvoiceSetting::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson('/api/finance/invoice-settings', [
            'invoice_digits' => 15,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('invoice_digits');
    }
}
