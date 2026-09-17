<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreditNoteApiTest extends TestCase
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

    public function test_can_list_credit_notes(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        CreditNote::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson('/api/finance/credit-notes');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_credit_note(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/finance/credit-notes', [
            'client_id' => $client->id,
            'issue_date' => '2026-09-14',
            'sub_total' => 5000,
            'total' => 5000,
            'note' => 'Refund for cancelled order',
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('credit_notes', [
            'client_id' => $client->id,
            'company_id' => $this->company->id,
            'status' => 'open',
            'added_by' => $this->adminUser->id,
        ]);
    }

    public function test_cannot_create_credit_note_without_required_fields(): void
    {
        $response = $this->postJson('/api/finance/credit-notes', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['issue_date']);
    }

    public function test_can_show_credit_note(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $creditNote = CreditNote::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson("/api/finance/credit-notes/{$creditNote->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $creditNote->id]]);
    }

    public function test_can_update_credit_note_status(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $creditNote = CreditNote::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->putJson("/api/finance/credit-notes/{$creditNote->id}", [
            'status' => 'closed',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_credit_note(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $creditNote = CreditNote::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->deleteJson("/api/finance/credit-notes/{$creditNote->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertSoftDeleted('credit_notes', ['id' => $creditNote->id, 'company_id' => $this->company->id]);
    }
}
