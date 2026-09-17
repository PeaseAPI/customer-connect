<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseRecurring;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpenseRecurringApiTest extends TestCase
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

    public function test_can_list_expense_recurrings(): void
    {
        $expense = Expense::factory()->create(['company_id' => $this->company->id]);
        ExpenseRecurring::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'expense_id' => $expense->id,
        ]);

        $response = $this->getJson('/api/finance/expense-recurrings');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_expense_recurring(): void
    {
        $expense = Expense::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/finance/expense-recurrings', [
            'expense_id' => $expense->id,
            'frequency' => 'monthly',
            'interval' => 1,
            'start_date' => '2026-10-01',
            'end_date' => '2027-09-30',
            'status' => 'active',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('expense_recurrings', [
            'expense_id' => $expense->id,
            'frequency' => 'monthly',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_expense_recurring_without_expense_id(): void
    {
        $response = $this->postJson('/api/finance/expense-recurrings', [
            'frequency' => 'monthly',
            'start_date' => '2026-10-01',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('expense_id');
    }

    public function test_cannot_create_expense_recurring_with_invalid_frequency(): void
    {
        $expense = Expense::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/finance/expense-recurrings', [
            'expense_id' => $expense->id,
            'frequency' => 'hourly',
            'start_date' => '2026-10-01',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('frequency');
    }

    public function test_can_show_expense_recurring(): void
    {
        $expense = Expense::factory()->create(['company_id' => $this->company->id]);
        $recurring = ExpenseRecurring::factory()->create([
            'company_id' => $this->company->id,
            'expense_id' => $expense->id,
        ]);

        $response = $this->getJson("/api/finance/expense-recurrings/{$recurring->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $recurring->id]]);
    }

    public function test_can_update_expense_recurring(): void
    {
        $expense = Expense::factory()->create(['company_id' => $this->company->id]);
        $recurring = ExpenseRecurring::factory()->create([
            'company_id' => $this->company->id,
            'expense_id' => $expense->id,
        ]);

        $response = $this->putJson("/api/finance/expense-recurrings/{$recurring->id}", [
            'frequency' => 'weekly',
            'status' => 'inactive',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('expense_recurrings', [
            'id' => $recurring->id,
            'frequency' => 'weekly',
            'status' => 'inactive',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_expense_recurring(): void
    {
        $expense = Expense::factory()->create(['company_id' => $this->company->id]);
        $recurring = ExpenseRecurring::factory()->create([
            'company_id' => $this->company->id,
            'expense_id' => $expense->id,
        ]);

        $response = $this->deleteJson("/api/finance/expense-recurrings/{$recurring->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('expense_recurrings', ['id' => $recurring->id]);
    }
}
