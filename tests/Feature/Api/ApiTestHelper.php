<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;

trait ApiTestHelper
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;

    protected function setUpApiTest(): void
    {
        // Create a company
        $this->company = Company::factory()->create([
            'status' => 'active',
        ]);

        // Create an admin user belonging to the company
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        $this->adminUser->assignRole('admin');

        // Set company context for scopes
        Context::add('current_company_id', $this->company->id);

        // Authenticate as admin
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    /**
     * Assert the response has a successful JSON structure.
     */
    protected function assertSuccessResponse($response, int $status = 200): void
    {
        $response->assertStatus($status);
        $response->assertJson(['success' => true]);
    }

    /**
     * Assert the response has a validation error (422).
     */
    protected function assertValidationError($response, ?string $field = null): void
    {
        $response->assertStatus(422);
        if ($field) {
            $response->assertJsonValidationErrors($field);
        }
    }
}
