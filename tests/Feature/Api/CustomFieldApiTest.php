<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\CustomField;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CustomFieldApiTest extends TestCase
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

    public function test_can_list_custom_fields(): void
    {
        CustomField::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/settings/custom-fields');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_custom_field(): void
    {
        $response = $this->postJson('/api/settings/custom-fields', [
            'field_name' => 'Priority Level',
            'field_type' => 'select',
            'module' => 'tasks',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('custom_fields', [
            'field_name' => 'Priority Level',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_custom_field_without_required_fields(): void
    {
        $response = $this->postJson('/api/settings/custom-fields', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['field_name', 'field_type', 'module']);
    }

    public function test_can_show_custom_field(): void
    {
        $field = CustomField::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/settings/custom-fields/{$field->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_custom_field(): void
    {
        $field = CustomField::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/settings/custom-fields/{$field->id}", [
            'field_name' => 'Updated Field',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_custom_field(): void
    {
        $field = CustomField::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/settings/custom-fields/{$field->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('custom_fields', ['id' => $field->id]);
    }
}
