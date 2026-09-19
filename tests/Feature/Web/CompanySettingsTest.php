<?php

namespace Tests\Feature\Web;

use App\Models\Company;
use App\Models\User;
use App\Support\Branding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Branding::flushResolved();
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    }

    protected function actingAsAdmin(Company $company): User
    {
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole('admin');

        $this->actingAs($user);

        return $user;
    }

    public function test_company_settings_page_renders_branding_fields(): void
    {
        $company = Company::factory()->create([
            'logo_background_color' => '#123456',
        ]);
        $this->actingAsAdmin($company);

        Http::fake([
            '*/api/companies/1' => Http::response([
                'success' => true,
                'data' => $company->fresh()->toArray(),
            ], 200),
        ]);

        $response = $this->get('/settings/company');

        $response->assertOk();
        $response->assertSee('Company Settings');
        $response->assertSee('Logo');
        $response->assertSee('logo_background_color', false);
        $response->assertSee('login_background', false);
    }

    public function test_update_company_stores_uploaded_logo_on_public_disk(): void
    {
        $company = Company::factory()->create();
        $this->actingAsAdmin($company);

        Storage::fake('public');
        Http::fake([
            '*/api/companies/1' => Http::response(['success' => true], 200),
        ]);

        $response = $this->put('/settings/company', [
            'company_name' => 'KHT Ltd',
            'company_email' => 'admin@kht.test',
            'company_phone' => '1234567890',
            'logo_background_color' => '#ABCDEF',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ]);

        $response->assertRedirect('/settings/company');

        $stored = Storage::disk('public')->files('company-branding');
        $this->assertCount(1, $stored, 'Uploaded logo should be stored on the public disk');

        Http::assertSent(function ($request) use ($stored) {
            return $request->url() === url('/api/companies/1')
                && $request->method() === 'PUT'
                && $request['logo'] === $stored[0]
                && $request['logo_background_color'] === '#ABCDEF'
                && $request['company_name'] === 'KHT Ltd';
        });
    }

    public function test_login_page_uses_company_branding_for_guests(): void
    {
        Company::factory()->create([
            'logo' => 'company-branding/custom-logo.png',
            'logo_background_color' => '#1B2A4E',
            'company_name' => 'KHT Branding Co',
        ]);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('login_header', false);
        $response->assertSee('login_box', false);
        $response->assertSee('company-branding/custom-logo.png');
        $response->assertSee('#1B2A4E');
        $response->assertSee('KHT Branding Co');
    }

    public function test_login_page_falls_back_to_template_logo_when_company_has_none(): void
    {
        Company::factory()->create(['logo' => null]);

        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('assets/images/logo.png');
    }
}
