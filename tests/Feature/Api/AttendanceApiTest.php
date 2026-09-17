<?php

namespace Tests\Feature\Api;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
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

        public function test_can_list_attendances(): void
    {
        // Create 3 different users to avoid unique constraint
        $users = User::factory()->count(3)->create(['company_id' => $this->company->id]);
        foreach ($users as $user) {
            Attendance::factory()->create([
                'company_id' => $this->company->id,
                'user_id' => $user->id,
            ]);
        }

        $response = $this->getJson('/api/hrm/attendances');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_show_attendance(): void
    {
        $attendance = Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);

        $response = $this->getJson("/api/hrm/attendances/{$attendance->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

        public function test_can_clock_in(): void
    {
        $response = $this->postJson('/api/hrm/attendances/clock-in');
        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->adminUser->id,
            'company_id' => $this->company->id,
        ]);
    }

                        public function test_can_clock_out_after_clock_in(): void
    {
        // Create attendance record with the exact date format that SQLite will store
        Attendance::create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'date' => now(),  // Use Carbon instance, not string - let the model handle formatting
            'clock_in_time' => now(),
        ]);

        // Clock out
        $response = $this->postJson('/api/hrm/attendances/clock-out');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_clock_out_returns_404_without_clock_in(): void
    {
        $response = $this->postJson('/api/hrm/attendances/clock-out');
        $response->assertStatus(404);
    }

    public function test_can_filter_attendances_by_date(): void
    {
        Attendance::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'date' => '2026-01-15',
        ]);

        $response = $this->getJson('/api/hrm/attendances?date=2026-01-15');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}

