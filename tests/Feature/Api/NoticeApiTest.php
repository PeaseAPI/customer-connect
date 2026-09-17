<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NoticeApiTest extends TestCase
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

    public function test_can_list_notices(): void
    {
        Notice::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/notices');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_notice(): void
    {
        $response = $this->postJson('/api/notices', [
            'heading' => 'Important Announcement',
            'description' => 'Please read carefully',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('notices', [
            'heading' => 'Important Announcement',
            'company_id' => $this->company->id,
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_notice_without_heading(): void
    {
        $response = $this->postJson('/api/notices', [
            'description' => 'Some content',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('heading');
    }

    public function test_can_show_notice(): void
    {
        $notice = Notice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/notices/{$notice->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $notice->id]]);
    }

    public function test_can_update_notice(): void
    {
        $notice = Notice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/notices/{$notice->id}", [
            'heading' => 'Updated Heading',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('notices', [
            'id' => $notice->id,
            'heading' => 'Updated Heading',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_notice(): void
    {
        $notice = Notice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/notices/{$notice->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('notices', ['id' => $notice->id, 'company_id' => $this->company->id]);
    }

    public function test_can_mark_notice_as_read(): void
    {
        $notice = Notice::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->postJson("/api/notices/{$notice->id}/mark-read");
        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('notice_views', [
            'notice_id' => $notice->id,
            'user_id' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }
}
