<?php

namespace App\Support;

use App\Models\Company;

/**
 * Central place for brand/visual identity that is managed from the admin
 * (Company settings), mirroring how the original Worksuite resolves
 * global_setting()->logo_url / logo_background_color / login_background.
 */
class Branding
{
    private static ?Company $memo = null;
    private static bool $resolved = false;

    public static function company(?Company $company = null): ?Company
    {
        if ($company !== null) {
            return $company;
        }

        if (auth()->check()) {
            return auth()->user()->company;
        }

        // Public pages (login/register) show the default company branding.
        // Memoized per request only — caching Eloquent models across requests
        // breaks unserialization (see __PHP_Incomplete_Class pitfalls).
        if (!self::$resolved) {
            self::$resolved = true;
            self::$memo = Company::query()->orderBy('id')->first();
        }

        return self::$memo;
    }

    /**
     * Reset the per-request memo. Long-lived worker processes (Octane, queue
     * workers, tests within one PHPUnit process) must call this when the
     * database state changes.
     */
    public static function flushResolved(): void
    {
        self::$memo = null;
        self::$resolved = false;
    }

    public static function logoUrl(?Company $company = null): string
    {
        $logo = self::company($company)?->logo;

        return $logo ? asset('storage/' . $logo) : asset('assets/images/logo.png');
    }

    public static function appName(?Company $company = null): string
    {
        return self::company($company)?->company_name ?: config('app.name', 'Customer Connect');
    }

    public static function logoBackgroundColor(?Company $company = null): string
    {
        return self::company($company)?->logo_background_color ?: '#FFFFFF';
    }

    public static function loginBackgroundUrl(?Company $company = null): ?string
    {
        $background = self::company($company)?->login_background;

        return $background ? asset('storage/' . $background) : null;
    }
}
