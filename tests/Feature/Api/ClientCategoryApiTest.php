<?php

namespace Tests\Feature\Api;

use App\Models\ClientCategory;
use App\Models\ClientSubCategory;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientCategoryApiTest extends TestCase
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

    public function test_can_list_client_categories(): void
    {
        ClientCategory::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/crm/client-categories');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_client_category(): void
    {
        $response = $this->postJson('/api/crm/client-categories', [
            'category_name' => 'VIP Clients',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('client_categories', [
            'category_name' => 'VIP Clients',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_category_without_name(): void
    {
        $response = $this->postJson('/api/crm/client-categories', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_name');
    }

    public function test_can_show_client_category(): void
    {
        $category = ClientCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/crm/client-categories/{$category->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_client_category(): void
    {
        $category = ClientCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/crm/client-categories/{$category->id}", [
            'category_name' => 'Updated Category',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_client_category(): void
    {
        $category = ClientCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/crm/client-categories/{$category->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('client_categories', ['id' => $category->id]);
    }

    public function test_can_list_sub_categories(): void
    {
        $category = ClientCategory::factory()->create(['company_id' => $this->company->id]);
        ClientSubCategory::factory()->count(2)->create([
            'company_id' => $this->company->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/crm/client-categories/{$category->id}/sub-categories");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_sub_category(): void
    {
        $category = ClientCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson("/api/crm/client-categories/{$category->id}/sub-categories", [
            'category_name' => 'Sub VIP',
            'description' => 'Sub category desc',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_can_show_sub_category(): void
    {
        $category = ClientCategory::factory()->create(['company_id' => $this->company->id]);
        $subCategory = ClientSubCategory::factory()->create([
            'company_id' => $this->company->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson("/api/crm/client-categories/{$category->id}/sub-categories/{$subCategory->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_sub_category(): void
    {
        $category = ClientCategory::factory()->create(['company_id' => $this->company->id]);
        $subCategory = ClientSubCategory::factory()->create([
            'company_id' => $this->company->id,
            'category_id' => $category->id,
        ]);

        $response = $this->putJson("/api/crm/client-categories/{$category->id}/sub-categories/{$subCategory->id}", [
            'category_name' => 'Updated Sub Category',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_sub_category(): void
    {
        $category = ClientCategory::factory()->create(['company_id' => $this->company->id]);
        $subCategory = ClientSubCategory::factory()->create([
            'company_id' => $this->company->id,
            'category_id' => $category->id,
        ]);

        $response = $this->deleteJson("/api/crm/client-categories/{$category->id}/sub-categories/{$subCategory->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('client_sub_categories', ['id' => $subCategory->id]);
    }
}
