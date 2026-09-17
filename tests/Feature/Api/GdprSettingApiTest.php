<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\GdprSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GdprSettingApiTest extends TestCase
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

    public function test_can_show_gdpr_settings(): void
    {
        GdprSetting::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/gdpr/settings');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_gdpr_settings(): void
    {
        GdprSetting::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson('/api/gdpr/settings', [
            'gdpr_enable' => true,
            'privacy_policy' => 'Updated privacy policy text',
            'show_consent_on_signup' => true,
            'allow_right_to_be_forgotten' => true,
            'allow_data_export' => false,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('gdpr_settings', [
            'company_id' => $this->company->id,
            'gdpr_enable' => 1,
            'allow_data_export' => 0,
        ]);
    }

    public function test_cannot_update_gdpr_settings_with_invalid_boolean(): void
    {
        GdprSetting::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson('/api/gdpr/settings', [
            'gdpr_enable' => 'not-a-boolean',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('gdpr_enable');
    }
}
