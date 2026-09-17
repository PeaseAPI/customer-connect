<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\EmergencyContact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmergencyContactApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->employee = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_emergency_contacts(): void
    {
        EmergencyContact::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->getJson("/api/hrm/employees/{$this->employee->id}/emergency-contacts");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_emergency_contact(): void
    {
        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/emergency-contacts", [
            'name' => 'Wang Fang',
            'mobile' => '13900139000',
            'relation' => 'Spouse',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('emergency_contacts', [
            'user_id' => $this->employee->id,
            'name' => 'Wang Fang',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_emergency_contact_without_name(): void
    {
        $response = $this->postJson("/api/hrm/employees/{$this->employee->id}/emergency-contacts", [
            'mobile' => '13900139000',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_can_show_emergency_contact(): void
    {
        $contact = EmergencyContact::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->getJson("/api/hrm/employees/{$this->employee->id}/emergency-contacts/{$contact->id}");
        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $contact->id]]);
    }

    public function test_can_update_emergency_contact(): void
    {
        $contact = EmergencyContact::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->putJson("/api/hrm/employees/{$this->employee->id}/emergency-contacts/{$contact->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('emergency_contacts', [
            'id' => $contact->id,
            'name' => 'Updated Name',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_emergency_contact(): void
    {
        $contact = EmergencyContact::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->employee->id,
        ]);

        $response = $this->deleteJson("/api/hrm/employees/{$this->employee->id}/emergency-contacts/{$contact->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('emergency_contacts', ['id' => $contact->id]);
    }
}
