<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductApiTest extends TestCase
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

    public function test_can_list_products(): void
    {
        Product::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/crm/products');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_product(): void
    {
        $response = $this->postJson('/api/crm/products', [
            'name' => 'Enterprise License',
            'price' => 999.99,
            'description' => 'Full enterprise license',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('products', [
            'name' => 'Enterprise License',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_product_without_name(): void
    {
        $response = $this->postJson('/api/crm/products', [
            'price' => 100,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_cannot_create_product_without_price(): void
    {
        $response = $this->postJson('/api/crm/products', [
            'name' => 'Some Product',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('price');
    }

    public function test_can_show_product(): void
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/crm/products/{$product->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $product->id]]);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/crm/products/{$product->id}", [
            'name' => 'Updated Product',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/crm/products/{$product->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
