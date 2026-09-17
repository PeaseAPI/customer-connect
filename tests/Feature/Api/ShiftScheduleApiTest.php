<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\EmployeeShiftSchedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ShiftScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected User $employee;
    protected Shift $shift;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->employee = User::factory()->create(['company_id' => $this->company->id]);
        $this->shift = Shift::factory()->create(['company_id' => $this->company->id]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_shift_schedules(): void
    {
        EmployeeShiftSchedule::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->getJson("/api/hrm/employees/{$this->employee->id}/shift-schedules");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_shift_schedule(): void
    {
                $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/shift-schedules", [
            'shift_id' => $this->shift->id,
            'date' => '2026-10-01',
            'day_of_week' => 'monday',
            'start_time' => '09:00',
            'end_time' => '17:00',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('employee_shift_schedules', [
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_shift_schedule_without_required_fields(): void
    {
        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/shift-schedules", []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['shift_id', 'date', 'day_of_week']);
    }

    public function test_can_update_shift_schedule(): void
    {
        $schedule = EmployeeShiftSchedule::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->putJson("/api/hrm/employees/{$this->employee->id}/shift-schedules/{$schedule->id}", [
            'shift_id' => $this->shift->id,
            'date' => '2026-10-02',
                        'day_of_week' => 'tuesday',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_shift_schedule(): void
    {
        $schedule = EmployeeShiftSchedule::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
            'shift_id' => $this->shift->id,
        ]);

        $response = $this->deleteJson("/api/hrm/employees/{$this->employee->id}/shift-schedules/{$schedule->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('employee_shift_schedules', ['id' => $schedule->id]);
    }
}
