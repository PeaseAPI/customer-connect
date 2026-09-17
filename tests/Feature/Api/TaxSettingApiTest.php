<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\TaxSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaxSettingApiTest extends TestCase
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

    public function test_can_list_tax_settings(): void
    {
        TaxSetting::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/settings/tax-settings');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_tax_setting(): void
    {
        $response = $this->postJson('/api/settings/tax-settings', [
            'tax_name' => 'VAT',
            'tax_percent' => 13.00,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('tax_settings', [
            'tax_name' => 'VAT',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_tax_setting_without_required_fields(): void
    {
        $response = $this->postJson('/api/settings/tax-settings', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tax_name', 'tax_percent']);
    }

    public function test_can_show_tax_setting(): void
    {
        $setting = TaxSetting::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/settings/tax-settings/{$setting->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_tax_setting(): void
    {
        $setting = TaxSetting::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/settings/tax-settings/{$setting->id}", [
            'tax_name' => 'Updated Tax',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_tax_setting(): void
    {
        $setting = TaxSetting::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/settings/tax-settings/{$setting->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('tax_settings', ['id' => $setting->id]);
    }
}
