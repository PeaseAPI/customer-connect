<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderApiTest extends TestCase
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

    public function test_can_list_orders(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        Order::factory()->count(3)->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

                $response = $this->getJson('/api/finance/orders');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_create_order(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);

        $response = $this->postJson('/api/finance/orders', [
            'client_id' => $client->id,
            'order_number' => 'ORD-001',
            'status' => 'pending',
            'sub_total' => 10000,
            'total' => 10000,
        ]);

        $response->assertStatus(201);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('orders', [
            'order_number' => 'ORD-001',
            'client_id' => $client->id,
            'company_id' => $this->company->id,
        ]);
    }

    public function test_cannot_create_order_without_required_fields(): void
    {
        $response = $this->postJson('/api/finance/orders', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['client_id', 'order_number']);
    }

    public function test_can_show_order(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->getJson("/api/finance/orders/{$order->id}");

        $response->assertOk();
        $response->assertJson(['success' => true, 'data' => ['id' => $order->id]]);
    }

    public function test_can_update_order(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->putJson("/api/finance/orders/{$order->id}", [
            'status' => 'completed',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_delete_order(): void
    {
        $client = User::factory()->create(['company_id' => $this->company->id]);
        $order = Order::factory()->create([
            'company_id' => $this->company->id,
            'client_id' => $client->id,
        ]);

        $response = $this->deleteJson("/api/finance/orders/{$order->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }
}
