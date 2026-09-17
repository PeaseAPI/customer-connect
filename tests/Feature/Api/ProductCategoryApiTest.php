<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductCategoryApiTest extends TestCase
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

    public function test_can_list_product_categories(): void
    {
        ProductCategory::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/crm/product-categories');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_product_category(): void
    {
        $response = $this->postJson('/api/crm/product-categories', [
            'category_name' => 'Software',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('product_categories', [
            'category_name' => 'Software',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_product_category_without_name(): void
    {
        $response = $this->postJson('/api/crm/product-categories', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_name');
    }

    public function test_can_show_product_category(): void
    {
        $category = ProductCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/crm/product-categories/{$category->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_product_category(): void
    {
        $category = ProductCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/crm/product-categories/{$category->id}", [
            'category_name' => 'Updated Category',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_product_category(): void
    {
        $category = ProductCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/crm/product-categories/{$category->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('product_categories', ['id' => $category->id]);
    }

    public function test_can_list_product_sub_categories(): void
    {
        $category = ProductCategory::factory()->create(['company_id' => $this->company->id]);
        ProductSubCategory::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/crm/product-categories/{$category->id}/sub-categories");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_product_sub_category(): void
    {
        $category = ProductCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson("/api/crm/product-categories/{$category->id}/sub-categories", [
            'category_name' => 'SaaS',
            'description' => 'Software as a Service',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_can_show_product_sub_category(): void
    {
        $category = ProductCategory::factory()->create(['company_id' => $this->company->id]);
        $subCategory = ProductSubCategory::factory()->create([
            'company_id' => $this->company->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/crm/product-categories/{$category->id}/sub-categories/{$subCategory->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_product_sub_category(): void
    {
        $category = ProductCategory::factory()->create(['company_id' => $this->company->id]);
        $subCategory = ProductSubCategory::factory()->create([
            'company_id' => $this->company->id,
            'category_id' => $category->id,
        ]);

        $response = $this->putJson("/api/crm/product-categories/{$category->id}/sub-categories/{$subCategory->id}", [
            'category_name' => 'Updated Sub Category',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_product_sub_category(): void
    {
        $category = ProductCategory::factory()->create(['company_id' => $this->company->id]);
        $subCategory = ProductSubCategory::factory()->create([
            'company_id' => $this->company->id,
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson("/api/crm/product-categories/{$category->id}/sub-categories/{$subCategory->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('product_sub_categories', ['id' => $subCategory->id]);
    }
}
