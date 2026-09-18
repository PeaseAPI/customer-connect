<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends BaseApiController
{
    public function login(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email', 'password' => 'required|string|min:6']);
        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages(['email' => ["Too many login attempts, please {$seconds} seconds before trying again"]]);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages(['email' => ['Email or password is incorrect']]);
        }
        if ($user->status !== UserStatus::Active) {
            throw ValidationException::withMessages(['email' => ['Account has been disabled']]);
        }

        // 如果用户启用了2FA，返回需要验证2FA的响应
        if ($user->two_factor_enabled && $user->two_factor_secret) {
            $tempToken = Str::random(40);
            Cache::put("2fa_pending:{$tempToken}", $user->id, now()->addMinutes(5));
            return $this->success([
                'requires_2fa' => true,
                'temp_token' => $tempToken,
            ], 'Please enter two-factor authentication code');
        }

        RateLimiter::clear($throttleKey);
        $user->update(['last_login' => now()]);
        Context::add('current_company_id', $user->company_id);
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success(['user' => $user->load(['company', 'employeeDetail', 'roles', 'permissions']), 'token' => $token, 'token_type' => 'Bearer'], 'Login successful');
    }

    /**
     * 验证双因素认证码
     */
    public function verify2fa(Request $request): JsonResponse
    {
        $request->validate([
            'temp_token' => 'required|string',
            'code' => 'required|string|size:6',
        ]);

        $userId = Cache::get("2fa_pending:{$request->temp_token}");
        if (!$userId) {
            return $this->error('Verification token expired, please log in again', 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return $this->error('User not found', 404);
        }

        // 支持恢复码
        $recoveryCodes = Cache::get("2fa_recovery:{$user->id}", []);
        if (in_array($request->code, $recoveryCodes)) {
            // 使用恢复码后移除
            $recoveryCodes = array_values(array_diff($recoveryCodes, [$request->code]));
            Cache::put("2fa_recovery:{$user->id}", $recoveryCodes, now()->addDays(30));
        } else {
            // 验证TOTP码
            $google2fa = new Google2FA();
            try {
                $secret = decrypt($user->two_factor_secret);
                $valid = $google2fa->verifyKey($secret, $request->code);
            } catch (\Exception $e) {
                $valid = false;
            }

            if (!$valid) {
                return $this->error('Verification code is incorrect', 400);
            }
        }

        Cache::forget("2fa_pending:{$request->temp_token}");
        $user->update(['last_login' => now()]);
        Context::add('current_company_id', $user->company_id);
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success(['user' => $user->load(['company', 'employeeDetail', 'roles', 'permissions']), 'token' => $token, 'token_type' => 'Bearer'], 'Login successful');
    }

    /**
     * 启用双因素认证 — 初始化（获取QR码）
     */
    public function enable2fa(Request $request): JsonResponse
    {
        $user = $request->user();
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        // 暂存secret，待验证后才正式启用
        Cache::put("2fa_setup:{$user->id}", $secret, now()->addMinutes(10));

        $qrCodeUrl = $google2fa->getQRCodeUrl(
                        config('app.name', 'Customer Connect'),
            $user->email,
            $secret
        );

        return $this->success([
            'secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
        ], 'Please scan the QR code with your authenticator app');
    }

    /**
     * 确认启用双因素认证
     */
    public function confirm2fa(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|size:6']);

        $user = $request->user();
        $secret = Cache::get("2fa_setup:{$user->id}");

        if (!$secret) {
            return $this->error('Setup session expired, please re-initialize', 400);
        }

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($secret, $request->code)) {
            return $this->error('Verification code is incorrect', 400);
        }

        $user->update([
            'two_factor_secret' => encrypt($secret),
            'two_factor_enabled' => true,
        ]);

        Cache::forget("2fa_setup:{$user->id}");

        // 生成恢复码
        $recoveryCodes = collect(range(1, 8))->map(fn() => Str::random(10))->toArray();
        Cache::put("2fa_recovery:{$user->id}", $recoveryCodes, now()->addDays(30));

        return $this->success([
            'recovery_codes' => $recoveryCodes,
        ], 'Two-factor authentication enabled, please save your recovery codes');
    }

    /**
     * 禁用双因素认证
     */
    public function disable2fa(Request $request): JsonResponse
    {
        $request->validate(['password' => 'required|string']);

        $user = $request->user();
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['password' => ['Password is incorrect']]);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ]);

        Cache::forget("2fa_recovery:{$user->id}");

        return $this->success(null, 'Two-factor authentication disabled');
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:191', 'company_email' => 'required|email|max:191',
            'name' => 'required|string|max:191', 'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20|unique:users,mobile', 'password' => 'required|string|min:8|confirmed',
        ]);
        $company = \App\Models\Company::create(['company_name' => $validated['company_name'], 'company_email' => $validated['company_email'], 'status' => \App\Enums\CompanyStatus::Active]);
                $user = User::create(['company_id' => $company->id, 'name' => $validated['name'], 'email' => $validated['email'], 'mobile' => $validated['mobile'] ?? null, 'password' => $validated['password'], 'status' => UserStatus::Active]);
        UserAuth::create(['user_id' => $user->id, 'company_id' => $company->id, 'is_superadmin' => true]);
        $user->assignRole('admin');
        \App\Models\OrganisationSetting::create(['company_id' => $company->id, 'company_name' => $validated['company_name'], 'company_email' => $validated['company_email']]);
        Context::add('current_company_id', $company->id);
        $token = $user->createToken('auth-token')->plainTextToken;
                return $this->success(['user' => $user->load(['company', 'roles']), 'token' => $token], 'Registration successful', 201);
    }

    public function sendSmsCode(Request $request): JsonResponse
    {
        $request->validate(['mobile' => 'required|string|max:20', 'purpose' => 'required|in:login,register,reset_password']);
        $throttleKey = 'sms:'.$request->mobile;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) { return $this->error('Sent too frequently', 429); }
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        cache()->put("sms_code:{$request->mobile}:{$request->purpose}", $code, now()->addMinutes(5));
        RateLimiter::hit($throttleKey, 60);
        return $this->success(null, 'Verification code sent');
    }

    public function loginWithSms(Request $request): JsonResponse
    {
        $request->validate(['mobile' => 'required|string|max:20', 'code' => 'required|string|size:6']);
        $cachedCode = cache()->get("sms_code:{$request->mobile}:login");
        if (!$cachedCode || $cachedCode !== $request->code) { return $this->error('Verification code is incorrect', 400); }
        cache()->forget("sms_code:{$request->mobile}:login");
        $user = User::where('mobile', $request->mobile)->first();
        if (!$user) return $this->error('This phone number is not registered', 404);
        if ($user->status !== UserStatus::Active) return $this->error('Account has been disabled', 403);
        $user->update(['last_login' => now()]);
        Context::add('current_company_id', $user->company_id);
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success(['user' => $user->load(['company', 'roles']), 'token' => $token], 'Login successful');
    }

    public function loginWithWechat(Request $request): JsonResponse { return $this->error('WeChat login not yet available', 501); }
    public function loginWithDingtalk(Request $request): JsonResponse { return $this->error('DingTalk login not yet available', 501); }
    public function loginWithFeishu(Request $request): JsonResponse { return $this->error('Feishu login not yet available', 501); }
    public function loginWithWework(Request $request): JsonResponse { return $this->error('WeCom login not yet available', 501); }

    /**
     * 发送Email验证码
     */
    public function sendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return $this->error('Email verified', 400);
        }
        $throttleKey = "email_verify:{$user->id}";
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return $this->error('Sent too frequently, please try again later', 429);
        }
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("email_verify:{$user->id}", $code, now()->addMinutes(10));
        Mail::raw(
            "Your email verification code is：{$code}，Valid for 10 minutes.",
            fn($message) => $message->to($user->email)->subject('Email Verification - ' . config('app.name'))
        );
        RateLimiter::hit($throttleKey, 60);
        return $this->success(null, 'Verification email sent');
    }

    /**
     * 验证Email
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = $request->user();
        $cachedCode = Cache::get("email_verify:{$user->id}");
        if (!$cachedCode || $cachedCode !== $request->code) {
            return $this->error('Verification code is incorrect', 400);
        }
        $user->markEmailAsVerified();
        Cache::forget("email_verify:{$user->id}");
        return $this->success(null, 'Email verified successfully');
    }

    /**
     * Admin手动验证用户Email
     */
    public function adminVerifyEmail(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->isAdmin() && !$request->user()->isSuperAdmin()) {
            return $this->error('No permission to operate', 403);
        }
        $user->markEmailAsVerified();
        return $this->success(null, 'Email manually verified');
    }
        public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $resetToken = Str::random(60);
            Cache::put("password_reset:{$resetToken}", [
                'user_id' => $user->id, 'email' => $user->email,
            ], now()->addHour());
            $resetUrl = config('app.frontend_url') . "/reset-password?token={$resetToken}";
            Mail::raw(
                "You are resetting your password, please click the link:\n\n{$resetUrl}\n\nThis link is valid for 1 hour.",
                fn($message) => $message->to($user->email)->subject('Password Reset - ' . config('app.name'))
            );
        }
        return $this->success(null, 'If this email is registered, a reset email has been sent');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $resetData = Cache::get("password_reset:{$validated['token']}");
        if (!$resetData) {
            return $this->error('Reset link has expired or is invalid', 400);
        }
        $user = User::find($resetData['user_id']);
        if (!$user) { return $this->error('User not found', 404); }
        $user->update(['password' => $validated['password']]);
        $user->tokens()->delete();
        Cache::forget("password_reset:{$validated['token']}");
        return $this->success(null, 'Password reset successfully');
    }

    /**
     * 获取用户所属的所有公司列表
     */
    public function companies(Request $request): JsonResponse
    {
        $user = $request->user();
        $companies = $user->userAuths()
            ->with('company:id,company_name,company_email,logo,status')
            ->get()
            ->map(fn($auth) => [
                'company_id' => $auth->company_id,
                'company_name' => $auth->company?->company_name,
                'company_email' => $auth->company?->company_email,
                'logo' => $auth->company?->logo,
                'status' => $auth->company?->status?->value,
                'is_superadmin' => $auth->is_superadmin,
                'is_current' => $auth->company_id === $user->company_id,
            ]);
        return $this->success($companies);
    }

    /**
     * 切换当前公司上下文
     */
    public function switchCompany(Request $request): JsonResponse
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        $user = $request->user();
        $companyId = $request->input('company_id');
        $userAuth = $user->userAuths()->where('company_id', $companyId)->first();
        if (!$userAuth) { return $this->error('You do not belong to this company', 403); }
        $user->update(['company_id' => $companyId]);
        Context::add('current_company_id', $companyId);
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success([
            'user' => $user->fresh()->load(['company', 'employeeDetail', 'roles', 'permissions']),
            'token' => $token, 'token_type' => 'Bearer',
        ], 'Company switched successfully');
    }

    /**
     * Invite user to register
     */
    public function inviteUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string|max:191',
            'role' => 'sometimes|string|in:admin,employee,client',
        ]);
        $user = $request->user();
        $inviteToken = Str::random(40);
        Cache::put("invite:{$inviteToken}", [
            'email' => $validated['email'], 'name' => $validated['name'],
            'role' => $validated['role'] ?? 'employee',
            'company_id' => $user->company_id, 'invited_by' => $user->id,
        ], now()->addDays(7));
        $inviteUrl = config('app.frontend_url') . "/register?invite_token={$inviteToken}";
        Mail::raw(
            "You have been invited to join {$user->company?->company_name}，Please click the link to complete registration：\n\n{$inviteUrl}\n\nThis link is valid for 7 days.",
            fn($message) => $message->to($validated['email'])->subject('Invitation to join - ' . config('app.name'))
        );
        return $this->success(['invite_token' => $inviteToken], 'Invitation email sent');
    }

    /**
     * Register via invitation link
     */
    public function registerWithInvite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invite_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'nullable|string|max:20',
        ]);
        $inviteData = Cache::get("invite:{$validated['invite_token']}");
        if (!$inviteData) { return $this->error('Invitation link has expired or is invalid', 400); }
        if (User::where('email', $inviteData['email'])->exists()) {
            return $this->error('This email is already registered', 400);
        }
        $user = User::create([
            'company_id' => $inviteData['company_id'], 'name' => $inviteData['name'],
            'email' => $inviteData['email'], 'mobile' => $validated['mobile'] ?? null,
            'password' => $validated['password'], 'status' => UserStatus::Active,
        ]);
        UserAuth::create(['user_id' => $user->id, 'company_id' => $inviteData['company_id'], 'is_superadmin' => false]);
        $user->assignRole($inviteData['role']);
        Cache::forget("invite:{$validated['invite_token']}");
        Context::add('current_company_id', $inviteData['company_id']);
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success(['user' => $user->load(['company', 'roles']), 'token' => $token], 'Registration successful', 201);
    }

    public function profile(Request $request): JsonResponse
    {
        return $this->success($request->user()->load(['company', 'employeeDetail.department', 'employeeDetail.designation', 'clientDetail', 'roles.permissions']));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'sometimes|string|max:191', 'mobile' => 'sometimes|string|max:20|unique:users,mobile,'.$request->user()->id, 'gender' => 'sometimes|in:male,female,other']);
        $request->user()->update($validated);
        return $this->success($request->user()->fresh(), 'Updated successfully');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate(['current_password' => 'required|string', 'password' => 'required|string|min:8|confirmed']);
        if (!Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => ['Current password is incorrect']]);
        }
                $request->user()->update(['password' => $request->password]);
        return $this->success(null, 'Password changed successfully');
    }

    public function logout(Request $request): JsonResponse { $request->user()->currentAccessToken()->delete(); return $this->success(null, 'Logged out successfully'); }
    public function logoutAll(Request $request): JsonResponse { $request->user()->tokens()->delete(); return $this->success(null, 'Logged out from all devices'); }

    /**
     * Accept employee invitation - set password and activate account
     */
    public function acceptEmployeeInvite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $inviteData = Cache::get("employee_invite:{$validated['token']}");
        if (!$inviteData) {
            return $this->error('Invitation link is invalid or expired', 400);
        }

        $user = User::create([
            'name' => $inviteData['name'],
            'email' => $inviteData['email'],
            'password' => $validated['password'],
            'company_id' => $inviteData['company_id'],
            'status' => UserStatus::Active,
        ]);

        if (!empty($inviteData['department_id']) || !empty($inviteData['designation_id'])) {
            $user->employeeDetail()->create([
                'department_id' => $inviteData['department_id'] ?? null,
                'designation_id' => $inviteData['designation_id'] ?? null,
            ]);
        }

        $user->assignRole($inviteData['role'] ?? 'employee');
        Cache::forget("employee_invite:{$validated['token']}");

        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success([
            'user' => $user->load(['employeeDetail', 'roles', 'permissions']),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Account activated successfully');
    }
}

