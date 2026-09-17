<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Holiday;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HolidayApiTest extends TestCase
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

    public function test_can_list_holidays(): void
    {
        Holiday::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/hrm/holidays');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_holiday(): void
    {
        $response = $this->postJson('/api/hrm/holidays', [
            'holiday_name' => 'Spring Festival',
            'date' => '2026-01-29',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('holidays', [
            'holiday_name' => 'Spring Festival',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_holiday_without_name(): void
    {
        $response = $this->postJson('/api/hrm/holidays', [
            'date' => '2026-01-29',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('holiday_name');
    }

    public function test_cannot_create_holiday_without_date(): void
    {
        $response = $this->postJson('/api/hrm/holidays', [
            'holiday_name' => 'Spring Festival',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('date');
    }

    public function test_can_show_holiday(): void
    {
        $holiday = Holiday::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/hrm/holidays/{$holiday->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $holiday->id]]);
    }

    public function test_can_update_holiday(): void
    {
        $holiday = Holiday::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/hrm/holidays/{$holiday->id}", [
            'holiday_name' => 'Updated Holiday',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('holidays', [
            'id' => $holiday->id,
            'holiday_name' => 'Updated Holiday',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_holiday(): void
    {
        $holiday = Holiday::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/hrm/holidays/{$holiday->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }
}
