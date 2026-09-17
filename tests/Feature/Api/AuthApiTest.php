<?php

namespace Tests\Feature\Api;

use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_register(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $response = $this->postJson('/api/auth/register', [
            'company_name' => 'Test Company',
            'company_email' => 'company@test.com',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

                        $response->assertStatus(201);
        $response->assertJson(['success' => true]);

        // Verify the user has a company_id set (multi-tenancy isolation)
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user->company_id);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'company_id' => $user->company_id,
        ]);
        $this->assertDatabaseHas('companies', ['company_email' => 'company@test.com']);
    }

    public function test_cannot_register_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'company_name' => 'Test Company',
            'company_email' => 'company@test.com',
            'name' => 'Test User',
            'email' => 'taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_can_login(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => UserStatus::Active,
        ]);
        $user->assignRole('admin');

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'data' => ['user', 'token']]);
    }

    public function test_cannot_login_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123'),
            'status' => UserStatus::Active,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_get_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->getJson('/api/user/profile');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_can_update_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->putJson('/api/user/profile', [
            'name' => 'Updated Name',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
                $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'company_id' => $user->company_id,
        ]);
    }

    public function test_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->putJson('/api/user/password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_cannot_change_password_with_wrong_current(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);
        Sanctum::actingAs($user, ['*']);

        $response = $this->putJson('/api/user/password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user, ['*']);

        $response = $this->postJson('/api/user/logout');

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}
