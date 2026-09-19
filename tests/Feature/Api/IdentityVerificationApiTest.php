<?php

namespace Tests\Feature\Api;

use App\Models\Company;
use App\Models\IdentityVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class IdentityVerificationApiTest extends TestCase
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

        // Enable the aliyun provider with fake credentials; all HTTP calls are faked below.
        config([
            'services.identity_verify.aliyun.enabled' => true,
            'services.identity_verify.aliyun.access_key_id' => 'test-ak',
            'services.identity_verify.aliyun.access_key_secret' => 'test-sk',
        ]);

        Http::fake([
            'idvi.cn-shanghai.aliyuncs.com/*' => Http::response([
                'Code' => '0',
                'Data' => ['IsConsistent' => 1, 'BizId' => 'biz-1'],
                'RequestId' => 'req-1',
            ]),
        ]);
    }

    public function test_id_card_creates_two_factor_record(): void
    {
        $response = $this->postJson('/api/identity-verification/id-card', [
            'name' => '张三',
            'id_number' => '110101199001011234',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.result', 'MATCH');

        $this->assertDatabaseHas('identity_verifications', [
            'provider' => 'aliyun',
            'type' => 'two_factor',
            'result' => 'MATCH',
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
        ]);
    }

    public function test_phone_three_factor_creates_three_factor_record(): void
    {
        $response = $this->postJson('/api/identity-verification/phone-three-factor', [
            'name' => '张三',
            'phone' => '13800138000',
            'id_number' => '110101199001011234',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('identity_verifications', [
            'provider' => 'aliyun',
            'type' => 'three_factor',
            'result' => 'MATCH',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_bank_card_creates_bank_card_record(): void
    {
        $response = $this->postJson('/api/identity-verification/bank-card', [
            'name' => '张三',
            'bank_card' => '6222020200112233445',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.result', 'MATCH');

        $this->assertDatabaseHas('identity_verifications', [
            'provider' => 'aliyun',
            'type' => 'bank_card',
            'result' => 'MATCH',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_bank_card_is_unsupported_on_alipay(): void
    {
        $response = $this->postJson('/api/identity-verification/bank-card', [
            'name' => '张三',
            'bank_card' => '6222020200112233445',
            'provider' => 'alipay',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('data.result', 'UNSUPPORTED');

        $this->assertDatabaseMissing('identity_verifications', [
            'type' => 'bank_card',
            'company_id' => $this->company->id,
        ]);
    }

    public function test_provider_must_be_whitelisted(): void
    {
        $response = $this->postJson('/api/identity-verification/bank-card', [
            'name' => '张三',
            'bank_card' => '6222020200112233445',
            'provider' => 'not-a-vendor',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('provider');
    }

    public function test_id_card_requires_id_number(): void
    {
        $response = $this->postJson('/api/identity-verification/id-card', [
            'name' => '张三',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('id_number');
    }

    public function test_status_returns_dual_service_state(): void
    {
        $response = $this->getJson('/api/identity-verification/status');

        $response->assertOk();
        $response->assertJson(['success' => true]);
        // Dual-service structure + legacy flat fields (backward compat)
        $response->assertJsonPath('data.phone_verify.current_provider', fn ($v) => is_string($v) && $v !== '');
        $response->assertJsonPath('data.identity_verify.current_provider', fn ($v) => is_string($v) && $v !== '');
        $response->assertJsonPath('data.current_provider', fn ($v) => is_string($v) && $v !== '');
    }

    public function test_index_filters_by_bank_card_type(): void
    {
        IdentityVerification::create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'provider' => 'aliyun',
            'type' => 'two_factor',
            'name' => '张三',
            'phone' => '',
            'result' => 'MATCH',
        ]);
        $bankRecordId = IdentityVerification::create([
            'company_id' => $this->company->id,
            'user_id' => $this->adminUser->id,
            'provider' => 'aliyun',
            'type' => 'bank_card',
            'name' => '张三',
            'phone' => '',
            'result' => 'MATCH',
        ])->id;

        $response = $this->getJson('/api/identity-verifications?type=bank_card');

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $types = collect($response->json('data'))->pluck('type')->unique()->values()->all();
        $this->assertSame(['bank_card'], $types);
        $this->assertContains($bankRecordId, collect($response->json('data'))->pluck('id')->all());
    }
}
