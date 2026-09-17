<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpenseApiTest extends TestCase
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

    public function test_can_list_expenses(): void
    {
        Expense::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/finance/expenses');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_expense(): void
    {
        $response = $this->postJson('/api/finance/expenses', [
            'item_name' => 'Office Supplies',
            'amount' => 1500.50,
            'purchase_date' => '2026-09-14',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('expenses', [
            'item_name' => 'Office Supplies',
            'amount' => 1500.50,
            'company_id' => $this->company->id,
            'created_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_expense_without_required_fields(): void
    {
        $response = $this->postJson('/api/finance/expenses', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['item_name', 'amount', 'purchase_date']);
    }

    public function test_can_create_expense_with_optional_fields(): void
    {
        $category = ExpenseCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->postJson('/api/finance/expenses', [
            'item_name' => 'Software License',
            'amount' => 5000,
            'purchase_date' => '2026-09-14',
            'category_id' => $category->id,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('expenses', [
            'item_name' => 'Software License',
            'category_id' => $category->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_show_expense(): void
    {
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/finance/expenses/{$expense->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $expense->id]]);
    }

    public function test_can_update_expense_status(): void
    {
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'pending',
        ]);

        $response = $this->putJson("/api/finance/expenses/{$expense->id}", [
            'status' => 'approved',
        ]);

                $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'approved',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_update_expense_with_invalid_status(): void
    {
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/finance/expenses/{$expense->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status');
    }

    public function test_can_delete_expense(): void
    {
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/finance/expenses/{$expense->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('expenses', ['id' => $expense->id, 'company_id' => $this->company->id]);
    }

    public function test_can_approve_expense(): void
    {
        $expense = Expense::factory()->create([
            'company_id' => $this->company->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/finance/expenses/{$expense->id}/approve");

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'approved',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_access_expenses_when_unauthenticated(): void
    {
        // Do not authenticate — clear the acting user
        auth()->forgetGuards();

        $response = $this->getJson('/api/finance/expenses');

        $response->assertUnauthorized();
    }

    public function test_expenses_are_scoped_to_company(): void
    {
        Expense::factory()->create(['company_id' => $this->company->id]);

        $otherCompany = Company::factory()->create();
        $otherExpense = Expense::factory()->create([
            'company_id' => $otherCompany->id,
        ]);

        $response = $this->getJson('/api/finance/expenses');

        $response->assertOk();
        $expenseIds = collect($response->json('data'))->pluck('id');
        $this->assertFalse($expenseIds->contains($otherExpense->id));
    }
}
