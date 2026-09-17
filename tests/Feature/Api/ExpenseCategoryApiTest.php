<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExpenseCategoryApiTest extends TestCase
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

    public function test_can_list_expense_categories(): void
    {
        ExpenseCategory::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/finance/expense-categories');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_expense_category(): void
    {
        $response = $this->postJson('/api/finance/expense-categories', [
            'category_name' => 'Office Supplies',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('expense_categories', [
            'category_name' => 'Office Supplies',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_expense_category_without_name(): void
    {
        $response = $this->postJson('/api/finance/expense-categories', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_name');
    }

    public function test_can_show_expense_category(): void
    {
        $category = ExpenseCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/finance/expense-categories/{$category->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $category->id]]);
    }

    public function test_can_update_expense_category(): void
    {
        $category = ExpenseCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/finance/expense-categories/{$category->id}", [
            'category_name' => 'Updated Category',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('expense_categories', [
            'id' => $category->id,
            'category_name' => 'Updated Category',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_expense_category(): void
    {
        $category = ExpenseCategory::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/finance/expense-categories/{$category->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('expense_categories', ['id' => $category->id]);
    }
}
