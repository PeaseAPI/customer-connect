<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\ContractTemplate;
use App\Models\ContractType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ContractTemplateApiTest extends TestCase
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

    public function test_can_list_contract_templates(): void
    {
        ContractTemplate::factory()->count(3)->create(['company_id' => $this->company->id]);

        $response = $this->getJson('/api/finance/contract-templates');
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_contract_template(): void
    {
        $response = $this->postJson('/api/finance/contract-templates', [
            'subject' => 'Service Agreement',
            'contract_detail' => '<p>Contract content here</p>',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('contract_templates', [
            'subject' => 'Service Agreement',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_template_without_subject(): void
    {
        $response = $this->postJson('/api/finance/contract-templates', []);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('subject');
    }

    public function test_can_show_contract_template(): void
    {
        $template = ContractTemplate::factory()->create(['company_id' => $this->company->id]);

        $response = $this->getJson("/api/finance/contract-templates/{$template->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_contract_template(): void
    {
        $template = ContractTemplate::factory()->create(['company_id' => $this->company->id]);

        $response = $this->putJson("/api/finance/contract-templates/{$template->id}", [
            'subject' => 'Updated Template',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_contract_template(): void
    {
        $template = ContractTemplate::factory()->create(['company_id' => $this->company->id]);

        $response = $this->deleteJson("/api/finance/contract-templates/{$template->id}");
        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('contract_templates', ['id' => $template->id]);
    }
}
