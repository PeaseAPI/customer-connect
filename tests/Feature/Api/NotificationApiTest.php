<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NotificationApiTest extends TestCase
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

    public function test_can_list_notifications(): void
    {
        Notification::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_mark_notification_as_read(): void
    {
        $notification = Notification::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->putJson("/api/notifications/{$notification->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_can_delete_notification(): void
    {
        $notification = Notification::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->deleteJson("/api/notifications/{$notification->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        Notification::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->postJson('/api/notifications/mark-all-read');

        $response->assertOk();
        $response->assertJson(['success' => true]);

        // All notifications should have read_at set
        $this->assertEquals(0, Notification::where('user_id', $this->adminUser->id)
            ->whereNull('read_at')->count());
    }

    public function test_can_get_unread_count(): void
    {
        Notification::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data' => ['count']]);
    }
}
