<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ModuleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
    }

    public function test_module_middleware_allows_access_when_module_enabled_in_package(): void
    {
        $package = Package::factory()->create([
            'modules' => ['hrm', 'pm', 'finance', 'crm'],
        ]);
        $company = Company::factory()->create(['package_id' => $package->id]);
        Subscription::factory()->create([
            'company_id' => $company->id,
            'package_id' => $package->id,
            'status' => 'active',
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('admin');

        Context::add('current_company_id', $company->id);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/hrm/awards');
        $response->assertOk();
    }

    public function test_module_middleware_blocks_access_when_module_not_in_package(): void
    {
        $package = Package::factory()->create([
            'modules' => ['hrm'], // No 'pm'
        ]);
        $company = Company::factory()->create(['package_id' => $package->id]);
        Subscription::factory()->create([
            'company_id' => $company->id,
            'package_id' => $package->id,
            'status' => 'active',
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('admin');

        Context::add('current_company_id', $company->id);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/pm/projects');
        $response->assertStatus(403);
        $response->assertJson(['message' => 'Module [pm] not enabled, please upgrade your plan']);
    }

    public function test_module_middleware_allows_access_for_super_admin(): void
    {
        $package = Package::factory()->create([
            'modules' => [], // No modules enabled
        ]);
        $company = Company::factory()->create(['package_id' => $package->id]);
        Subscription::factory()->create([
            'company_id' => $company->id,
            'package_id' => $package->id,
            'status' => 'active',
        ]);
        $superAdmin = User::factory()->create(['company_id' => $company->id]);
        $superAdmin->assignRole('super-admin');

        Context::add('current_company_id', $company->id);
        Sanctum::actingAs($superAdmin, ['*']);

        $response = $this->getJson('/api/finance/expense-categories');
        $response->assertOk();
    }

    public function test_module_middleware_allows_access_when_no_subscription(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('admin');

        Context::add('current_company_id', $company->id);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/finance/expense-categories');
        $response->assertOk();
    }

    public function test_finance_module_blocked_when_not_in_package(): void
    {
        $package = Package::factory()->create([
            'modules' => ['hrm'],
        ]);
        $company = Company::factory()->create(['package_id' => $package->id]);
        Subscription::factory()->create([
            'company_id' => $company->id,
            'package_id' => $package->id,
            'status' => 'active',
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('admin');

        Context::add('current_company_id', $company->id);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/finance/expense-categories');
        $response->assertStatus(403);
    }

    public function test_crm_module_blocked_when_not_in_package(): void
    {
        $package = Package::factory()->create([
            'modules' => ['hrm'],
        ]);
        $company = Company::factory()->create(['package_id' => $package->id]);
        Subscription::factory()->create([
            'company_id' => $company->id,
            'package_id' => $package->id,
            'status' => 'active',
        ]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('admin');

        Context::add('current_company_id', $company->id);
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/crm/estimate-requests');
        $response->assertStatus(403);
    }
}
