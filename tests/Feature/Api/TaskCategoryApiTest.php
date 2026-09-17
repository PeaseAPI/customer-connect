<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TaskCategoryApiTest extends TestCase
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

    public function test_can_list_task_categories(): void
    {
        TaskCategory::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/pm/task-categories');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_task_category(): void
    {
        $response = $this->postJson('/api/pm/task-categories', [
            'category_name' => 'Bug Fix',
            'color' => '#FF5722',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('task_categories', [
            'category_name' => 'Bug Fix',
            'company_id' => $this->company->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_task_category_without_name(): void
    {
        $response = $this->postJson('/api/pm/task-categories', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('category_name');
    }

    public function test_can_update_task_category(): void
    {
        $category = TaskCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/pm/task-categories/{$category->id}", [
            'category_name' => 'Updated Category',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_task_category(): void
    {
        $category = TaskCategory::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/pm/task-categories/{$category->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('task_categories', ['id' => $category->id]);
    }
}
