<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProjectCategoryApiTest extends TestCase
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

    public function test_can_list_project_categories(): void
    {
        ProjectCategory::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/pm/project-categories');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_project_category(): void
    {
        $response = $this->postJson('/api/pm/project-categories', [
            'category_name' => 'Internal Project',
            'color' => '#2196F3',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('project_categories', [
            'category_name' => 'Internal Project',
            'company_id' => $this->company->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_project_category_without_name(): void
    {
        $response = $this->postJson('/api/pm/project-categories', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_name');
    }

    public function test_can_update_project_category(): void
    {
        $category = ProjectCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/pm/project-categories/{$category->id}", [
            'category_name' => 'Updated Category',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_project_category(): void
    {
        $category = ProjectCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/pm/project-categories/{$category->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('project_categories', ['id' => $category->id]);
    }
}
