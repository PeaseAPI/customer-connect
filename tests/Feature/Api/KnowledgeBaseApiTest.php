<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\KnowledgeBase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KnowledgeBaseApiTest extends TestCase
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

    public function test_can_list_knowledge_base_articles(): void
    {
        KnowledgeBase::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/knowledge-bases');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_knowledge_base_article(): void
    {
        $response = $this->postJson('/api/knowledge-bases', [
            'title' => 'How to reset password',
            'description' => 'Step by step guide to reset your password',
            'status' => 'active',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('knowledge_bases', [
            'title' => 'How to reset password',
            'company_id' => $this->company->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_article_without_title(): void
    {
        $response = $this->postJson('/api/knowledge-bases', [
            'description' => 'No title article',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    public function test_can_show_article(): void
    {
        $article = KnowledgeBase::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/knowledge-bases/{$article->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $article->id]]);
    }

    public function test_can_update_article(): void
    {
        $article = KnowledgeBase::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/knowledge-bases/{$article->id}", [
            'title' => 'Updated Title',
            'status' => 'inactive',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_article(): void
    {
        $article = KnowledgeBase::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/knowledge-bases/{$article->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('knowledge_bases', ['id' => $article->id, 'company_id' => $this->company->id]);
    }
}
