<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShiftApiTest extends TestCase
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

    public function test_can_list_shifts(): void
    {
        Shift::factory()->count(3)->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson('/api/hrm/shifts');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_shift(): void
    {
        $response = $this->postJson('/api/hrm/shifts', [
            'shift_name' => 'Morning Shift',
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('shifts', [
            'shift_name' => 'Morning Shift',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_shift_without_name(): void
    {
        $response = $this->postJson('/api/hrm/shifts', [
            'start_time' => '08:00',
            'end_time' => '17:00',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('shift_name');
    }

    public function test_can_show_shift(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->getJson("/api/hrm/shifts/{$shift->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $shift->id]]);
    }

    public function test_can_update_shift(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->putJson("/api/hrm/shifts/{$shift->id}", [
            'shift_name' => 'Updated Shift',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('shifts', [
            'id' => $shift->id,
            'shift_name' => 'Updated Shift',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_shift(): void
    {
        $shift = Shift::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $response = $this->deleteJson("/api/hrm/shifts/{$shift->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('shifts', ['id' => $shift->id]);
    }
}
