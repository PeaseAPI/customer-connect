<?php

namespace Tests\Feature\Api;

use App\Models\ClientDetail;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Deal;
use App\Models\Department;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Holiday;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\PipelineStage;
use App\Models\Project;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanyScopeIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Company $companyA;
    protected Company $companyB;
    protected User $adminA;
    protected User $adminB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->companyA = Company::factory()->create();
        $this->companyB = Company::factory()->create();

        $this->adminA = User::factory()->create(['company_id' => $this->companyA->id]);
        $this->adminB = User::factory()->create(['company_id' => $this->companyB->id]);

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'client', 'guard_name' => 'web']);
        $this->adminA->assignRole('admin');
        $this->adminB->assignRole('admin');
    }

    protected function authenticateAsCompanyA(): void
    {
        Context::add('current_company_id', $this->companyA->id);
        Sanctum::actingAs($this->adminA, ['*']);
    }

    // ==========================================
    // LIST ISOLATION
    // ==========================================

    public function test_company_a_cannot_see_company_b_departments(): void
    {
        $deptB = Department::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/hrm/departments');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($deptB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_holidays(): void
    {
        $holidayB = Holiday::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/hrm/holidays');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($holidayB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_expense_categories(): void
    {
        $catB = ExpenseCategory::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/finance/expense-categories');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($catB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_invoices(): void
    {
        $clientB = User::factory()->create(['company_id' => $this->companyB->id]);
        $invoiceB = Invoice::factory()->create(['company_id' => $this->companyB->id, 'client_id' => $clientB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/finance/invoices');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($invoiceB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_projects(): void
    {
        $projectB = Project::factory()->create(['company_id' => $this->companyB->id, 'created_by' => $this->adminB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/pm/projects');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($projectB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_leaves(): void
    {
        $leaveTypeB = LeaveType::factory()->create(['company_id' => $this->companyB->id]);
        $leaveB = Leave::factory()->create(['company_id' => $this->companyB->id, 'user_id' => $this->adminB->id, 'leave_type_id' => $leaveTypeB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/hrm/leaves');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($leaveB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_leads(): void
    {
        $leadB = Lead::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/crm/leads');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($leadB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_expenses(): void
    {
        $expenseB = Expense::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/finance/expenses');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($expenseB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_contracts(): void
    {
        $clientB = User::factory()->create(['company_id' => $this->companyB->id]);
        $contractB = Contract::factory()->create(['company_id' => $this->companyB->id, 'client_id' => $clientB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/finance/contracts');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($contractB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_tickets(): void
    {
        $ticketB = Ticket::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/tickets');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($ticketB->id, $ids);
    }

        public function test_company_a_cannot_see_company_b_tasks(): void
    {
        $projectA = Project::factory()->create(['company_id' => $this->companyA->id, 'created_by' => $this->adminA->id]);
        $projectB = Project::factory()->create(['company_id' => $this->companyB->id, 'created_by' => $this->adminB->id]);
        $taskB = Task::factory()->create(['company_id' => $this->companyB->id, 'project_id' => $projectB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson("/api/pm/projects/{$projectA->id}/tasks");
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($taskB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_deals(): void
    {
        $pipelineStageB = PipelineStage::factory()->create(['company_id' => $this->companyB->id]);
        $dealB = Deal::factory()->create(['company_id' => $this->companyB->id, 'pipeline_stage_id' => $pipelineStageB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/crm/deals');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($dealB->id, $ids);
    }

    public function test_company_a_cannot_see_company_b_clients(): void
    {
                $clientB = User::factory()->create(['company_id' => $this->companyB->id]);
        $clientB->assignRole('client');
        ClientDetail::factory()->create(['user_id' => $clientB->id, 'company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson('/api/crm/clients');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($clientB->id, $ids);
    }

    // ==========================================
    // SHOW ISOLATION
    // ==========================================

    public function test_company_a_cannot_view_company_b_department(): void
    {
        $deptB = Department::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson("/api/hrm/departments/{$deptB->id}");
        $response->assertNotFound();
    }

    public function test_company_a_cannot_view_company_b_lead(): void
    {
        $leadB = Lead::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson("/api/crm/leads/{$leadB->id}");
        $response->assertNotFound();
    }

    public function test_company_a_cannot_view_company_b_expense(): void
    {
        $expenseB = Expense::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson("/api/finance/expenses/{$expenseB->id}");
        $response->assertNotFound();
    }

    public function test_company_a_cannot_view_company_b_contract(): void
    {
        $clientB = User::factory()->create(['company_id' => $this->companyB->id]);
        $contractB = Contract::factory()->create(['company_id' => $this->companyB->id, 'client_id' => $clientB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson("/api/finance/contracts/{$contractB->id}");
        $response->assertNotFound();
    }

    public function test_company_a_cannot_view_company_b_ticket(): void
    {
        $ticketB = Ticket::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson("/api/tickets/{$ticketB->id}");
        $response->assertNotFound();
    }

    public function test_company_a_cannot_view_company_b_project(): void
    {
        $projectB = Project::factory()->create(['company_id' => $this->companyB->id, 'created_by' => $this->adminB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson("/api/pm/projects/{$projectB->id}");
        $response->assertNotFound();
    }

    public function test_company_a_cannot_view_company_b_deal(): void
    {
        $pipelineStageB = PipelineStage::factory()->create(['company_id' => $this->companyB->id]);
        $dealB = Deal::factory()->create(['company_id' => $this->companyB->id, 'pipeline_stage_id' => $pipelineStageB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->getJson("/api/crm/deals/{$dealB->id}");
        $response->assertNotFound();
    }

    // ==========================================
    // UPDATE ISOLATION
    // ==========================================

    public function test_company_a_cannot_update_company_b_department(): void
    {
        $deptB = Department::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->putJson("/api/hrm/departments/{$deptB->id}", ['department_name' => 'Hacked']);
        $response->assertNotFound();
        $this->assertDatabaseMissing('departments', ['id' => $deptB->id, 'department_name' => 'Hacked']);
    }

    public function test_company_a_cannot_update_company_b_lead(): void
    {
        $leadB = Lead::factory()->create(['company_id' => $this->companyB->id, 'lead_name' => 'Original']);
        $this->authenticateAsCompanyA();

        $response = $this->putJson("/api/crm/leads/{$leadB->id}", ['lead_name' => 'Hacked']);
        $response->assertNotFound();
        $this->assertDatabaseMissing('leads', ['id' => $leadB->id, 'lead_name' => 'Hacked']);
    }

    public function test_company_a_cannot_update_company_b_expense(): void
    {
        $expenseB = Expense::factory()->create(['company_id' => $this->companyB->id, 'status' => 'pending']);
        $this->authenticateAsCompanyA();

        $response = $this->putJson("/api/finance/expenses/{$expenseB->id}", ['status' => 'approved']);
        $response->assertNotFound();
        $this->assertDatabaseHas('expenses', ['id' => $expenseB->id, 'status' => 'pending']);
    }

    public function test_company_a_cannot_update_company_b_contract(): void
    {
        $clientB = User::factory()->create(['company_id' => $this->companyB->id]);
        $contractB = Contract::factory()->create([
            'company_id' => $this->companyB->id,
            'client_id' => $clientB->id,
            'subject' => 'Original',
        ]);
        $this->authenticateAsCompanyA();

        $response = $this->putJson("/api/finance/contracts/{$contractB->id}", ['subject' => 'Hacked']);
        $response->assertNotFound();
        $this->assertDatabaseMissing('contracts', ['id' => $contractB->id, 'subject' => 'Hacked']);
    }

    public function test_company_a_cannot_update_company_b_ticket(): void
    {
        $ticketB = Ticket::factory()->create(['company_id' => $this->companyB->id, 'subject' => 'Original']);
        $this->authenticateAsCompanyA();

        $response = $this->putJson("/api/tickets/{$ticketB->id}", ['subject' => 'Hacked']);
        $response->assertNotFound();
                $this->assertDatabaseMissing('tickets', ['id' => $ticketB->id, 'subject' => 'Hacked']);
    }

    // ==========================================
    // DELETE ISOLATION
    // ==========================================

    public function test_company_a_cannot_delete_company_b_department(): void
    {
        $deptB = Department::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->deleteJson("/api/hrm/departments/{$deptB->id}");
        $response->assertNotFound();
        $this->assertDatabaseHas('departments', ['id' => $deptB->id]);
    }

    public function test_company_a_cannot_delete_company_b_lead(): void
    {
        $leadB = Lead::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->deleteJson("/api/crm/leads/{$leadB->id}");
        $response->assertNotFound();
        $this->assertDatabaseHas('leads', ['id' => $leadB->id]);
    }

    public function test_company_a_cannot_delete_company_b_expense(): void
    {
        $expenseB = Expense::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->deleteJson("/api/finance/expenses/{$expenseB->id}");
        $response->assertNotFound();
        $this->assertDatabaseHas('expenses', ['id' => $expenseB->id]);
    }

    public function test_company_a_cannot_delete_company_b_contract(): void
    {
        $clientB = User::factory()->create(['company_id' => $this->companyB->id]);
        $contractB = Contract::factory()->create(['company_id' => $this->companyB->id, 'client_id' => $clientB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->deleteJson("/api/finance/contracts/{$contractB->id}");
        $response->assertNotFound();
        $this->assertDatabaseHas('contracts', ['id' => $contractB->id]);
    }

    public function test_company_a_cannot_delete_company_b_ticket(): void
    {
        $ticketB = Ticket::factory()->create(['company_id' => $this->companyB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->deleteJson("/api/tickets/{$ticketB->id}");
        $response->assertNotFound();
        $this->assertDatabaseHas('tickets', ['id' => $ticketB->id]);
    }

    public function test_company_a_cannot_delete_company_b_project(): void
    {
        $projectB = Project::factory()->create(['company_id' => $this->companyB->id, 'created_by' => $this->adminB->id]);
        $this->authenticateAsCompanyA();

        $response = $this->deleteJson("/api/pm/projects/{$projectB->id}");
        $response->assertNotFound();
                $this->assertDatabaseHas('projects', ['id' => $projectB->id]);
    }

    // ==========================================
    // CREATE ISOLATION
    // ==========================================

    public function test_created_lead_is_scoped_to_company_a(): void
    {
        $this->authenticateAsCompanyA();

        $response = $this->postJson('/api/crm/leads', [
            'lead_name' => 'Company A Lead',
            'lead_email' => 'leadA@test.com',
            'lead_mobile' => '13800001111',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('leads', [
            'lead_name' => 'Company A Lead',
            'company_id' => $this->companyA->id,
        ]);
    }

    public function test_created_expense_is_scoped_to_company_a(): void
    {
        $this->authenticateAsCompanyA();

        $response = $this->postJson('/api/finance/expenses', [
            'item_name' => 'Company A Expense',
            'amount' => 100.00,
            'purchase_date' => '2026-09-16',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('expenses', [
            'item_name' => 'Company A Expense',
            'company_id' => $this->companyA->id,
        ]);
    }

    public function test_created_client_is_scoped_to_company_a(): void
    {
        $this->authenticateAsCompanyA();

        $response = $this->postJson('/api/crm/clients', [
            'name' => 'Company A Client',
            'email' => 'clientA@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'name' => 'Company A Client',
                        'email' => 'clientA@test.com',
            'company_id' => $this->companyA->id,
        ]);
    }

    // ==========================================
    // BIDIRECTIONAL ISOLATION
    // ==========================================

    public function test_company_b_cannot_see_company_a_leads(): void
    {
        $leadA = Lead::factory()->create(['company_id' => $this->companyA->id]);

        Context::add('current_company_id', $this->companyB->id);
        Sanctum::actingAs($this->adminB, ['*']);

        $response = $this->getJson('/api/crm/leads');
        $response->assertOk();

        $ids = collect($response->json('data.data'))->pluck('id');
        $this->assertNotContains($leadA->id, $ids);
    }

    public function test_company_b_cannot_view_company_a_expense(): void
    {
        $expenseA = Expense::factory()->create(['company_id' => $this->companyA->id]);

        Context::add('current_company_id', $this->companyB->id);
        Sanctum::actingAs($this->adminB, ['*']);

        $response = $this->getJson("/api/finance/expenses/{$expenseA->id}");
        $response->assertNotFound();
    }

    public function test_company_b_cannot_delete_company_a_ticket(): void
    {
        $ticketA = Ticket::factory()->create(['company_id' => $this->companyA->id]);

        Context::add('current_company_id', $this->companyB->id);
        Sanctum::actingAs($this->adminB, ['*']);

        $response = $this->deleteJson("/api/tickets/{$ticketA->id}");
        $response->assertNotFound();

        $this->assertDatabaseHas('tickets', ['id' => $ticketA->id]);
    }

    // ==========================================
    // EXPENSE APPROVAL ISOLATION
    // ==========================================

    public function test_company_a_cannot_approve_company_b_expense(): void
    {
        $expenseB = Expense::factory()->create(['company_id' => $this->companyB->id, 'status' => 'pending']);
        $this->authenticateAsCompanyA();

        $response = $this->postJson("/api/finance/expenses/{$expenseB->id}/approve");
        $response->assertNotFound();

        $this->assertDatabaseHas('expenses', ['id' => $expenseB->id, 'status' => 'pending']);
    }
}
