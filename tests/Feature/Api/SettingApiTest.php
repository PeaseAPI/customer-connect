<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\OrganisationSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected OrganisationSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->setting = OrganisationSetting::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_get_organisation_settings(): void
    {
        $response = $this->getJson('/api/settings/organisation');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_organisation_settings(): void
    {
        $response = $this->putJson('/api/settings/organisation', [
            'company_name' => 'Updated Org Name',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('organisation_settings', [
            'id' => $this->setting->id,
            'company_name' => 'Updated Org Name',
            'company_id' => $this->company->id,
        ]);
    }
}
