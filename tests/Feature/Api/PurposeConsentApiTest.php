<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\PurposeConsent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurposeConsentApiTest extends TestCase
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

    public function test_can_list_purpose_consents(): void
    {
        PurposeConsent::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/purpose-consents');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_purpose_consent(): void
    {
        $response = $this->postJson('/api/purpose-consents', [
            'name' => 'Marketing Consent',
            'description' => 'Consent for marketing emails',
            'is_default' => false,
            'is_active' => true,
            'allow_opt_out' => true,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('purpose_consents', [
            'name' => 'Marketing Consent',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_purpose_consent_without_name(): void
    {
        $response = $this->postJson('/api/purpose-consents', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_can_show_purpose_consent(): void
    {
        $consent = PurposeConsent::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/purpose-consents/{$consent->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $consent->id]]);
    }

    public function test_can_update_purpose_consent(): void
    {
        $consent = PurposeConsent::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/purpose-consents/{$consent->id}", [
            'name' => 'Updated Consent Name',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('purpose_consents', [
            'id' => $consent->id,
            'name' => 'Updated Consent Name',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_can_delete_purpose_consent(): void
    {
        $consent = PurposeConsent::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/purpose-consents/{$consent->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('purpose_consents', ['id' => $consent->id]);
    }

    public function test_can_create_removal_request(): void
    {
        $response = $this->postJson('/api/removal-requests', [
            'user_id' => $this->adminUser->id,
            'reason' => 'Right to be forgotten',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
    }

    public function test_can_list_removal_requests(): void
    {
        $response = $this->getJson('/api/removal-requests');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
