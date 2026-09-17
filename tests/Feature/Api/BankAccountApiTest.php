<?php

namespace Tests\Feature\Api;

use App\Models\BankAccount;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BankAccountApiTest extends TestCase
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

    public function test_can_list_bank_accounts(): void
    {
        BankAccount::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/finance/bank-accounts');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_bank_account(): void
    {
        $response = $this->postJson('/api/finance/bank-accounts', [
            'bank_name' => 'China Construction Bank',
            'account_name' => 'KHT Tech',
            'account_number' => '6227001234567890',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('bank_accounts', [
            'bank_name' => 'China Construction Bank',
            'company_id' => $this->company->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_bank_account_without_bank_name(): void
    {
        $response = $this->postJson('/api/finance/bank-accounts', [
            'account_name' => 'KHT Tech',
            'account_number' => '1234567890',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('bank_name');
    }

    public function test_can_show_bank_account(): void
    {
        $account = BankAccount::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/finance/bank-accounts/{$account->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $account->id]]);
    }

    public function test_can_update_bank_account(): void
    {
        $account = BankAccount::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/finance/bank-accounts/{$account->id}", [
            'bank_name' => 'ICBC',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('bank_accounts', [
            'id' => $account->id,
            'bank_name' => 'ICBC',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_bank_account(): void
    {
        $account = BankAccount::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/finance/bank-accounts/{$account->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('bank_accounts', ['id' => $account->id]);
    }

    public function test_can_filter_bank_accounts_by_status(): void
    {
        BankAccount::factory()->create(['company_id' => $this->company->id, 'status' => 'active']);
        BankAccount::factory()->create(['company_id' => $this->company->id, 'status' => 'inactive']);

        $response = $this->getJson('/api/finance/bank-accounts?status=active');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
