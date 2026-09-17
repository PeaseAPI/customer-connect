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

class CompanyApiTest extends TestCase
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

    public function test_can_list_companies(): void
    {
        $response = $this->getJson('/api/companies');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_show_company(): void
    {
        $response = $this->getJson("/api/companies/{$this->company->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_company(): void
    {
        $response = $this->putJson("/api/companies/{$this->company->id}", [
            'company_name' => 'Updated Company',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
