<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\EmployeeDocument;
use App\Models\EmployeeDocumentExpiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmployeeDocumentExpiryApiTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;
    protected User $adminUser;
    protected EmployeeDocument $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create();
        $this->adminUser = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $this->adminUser->assignRole('admin');

        $this->document = EmployeeDocument::factory()->create([
            'company_id' => $this->company->id,
        ]);

        Context::add('current_company_id', $this->company->id);
        Sanctum::actingAs($this->adminUser, ['*']);
    }

    public function test_can_list_document_expiries(): void
    {
        EmployeeDocumentExpiry::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'employee_document_id' => $this->document->id,
        ]);

        $response = $this->getJson('/api/hrm/document-expiries');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_show_document_expiry(): void
    {
        $expiry = EmployeeDocumentExpiry::factory()->create([
            'company_id' => $this->company->id,
            'employee_document_id' => $this->document->id,
        ]);

        $response = $this->getJson("/api/hrm/document-expiries/{$expiry->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_document_expiry(): void
    {
        $expiry = EmployeeDocumentExpiry::factory()->create([
            'company_id' => $this->company->id,
            'employee_document_id' => $this->document->id,
        ]);

        $response = $this->putJson("/api/hrm/document-expiries/{$expiry->id}", [
            'expiry_date' => '2027-12-31',
            'notified' => true,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('employee_document_expiries', [
            'id' => $expiry->id,
            'notified' => 1,
            'company_id' => $this->company->id,
        ]);
        $expiry->refresh();
        $this->assertEquals('2027-12-31', $expiry->expiry_date->toDateString());
    }

    public function test_can_delete_document_expiry(): void
    {
        $expiry = EmployeeDocumentExpiry::factory()->create([
            'company_id' => $this->company->id,
            'employee_document_id' => $this->document->id,
        ]);

        $response = $this->deleteJson("/api/hrm/document-expiries/{$expiry->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('employee_document_expiries', ['id' => $expiry->id]);
    }
}
