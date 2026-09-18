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
            throw ValidationException::withMessages(['email' => ["登录尝试次数过多，请 {$seconds} 秒后再试"]]);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages(['email' => ['邮箱或密码错误']]);
        }
        if ($user->status !== UserStatus::Active) {
            throw ValidationException::withMessages(['email' => ['账户已被禁用']]);
        }

        // 如果用户启用了2FA，返回需要验证2FA的响应
        if ($user->two_factor_enabled && $user->two_factor_secret) {
            $tempToken = Str::random(40);
            Cache::put("2fa_pending:{$tempToken}", $user->id, now()->addMinutes(5));
            return $this->success([
                'requires_2fa' => true,
                'temp_token' => $tempToken,
            ], '请输入双因素认证验证码');
        }

        RateLimiter::clear($throttleKey);
        $user->update(['last_login' => now()]);
        Context::add('current_company_id', $user->company_id);
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success(['user' => $user->load(['company', 'employeeDetail', 'roles', 'permissions']), 'token' => $token, 'token_type' => 'Bearer'], '登录成功');
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
            return $this->error('验证令牌已过期，请重新登录', 400);
        }

        $user = User::find($userId);
        if (!$user) {
            return $this->error('用户不存在', 404);
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
                return $this->error('验证码错误', 400);
            }
        }

        Cache::forget("2fa_pending:{$request->temp_token}");
        $user->update(['last_login' => now()]);
        Context::add('current_company_id', $user->company_id);
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success(['user' => $user->load(['company', 'employeeDetail', 'roles', 'permissions']), 'token' => $token, 'token_type' => 'Bearer'], '登录成功');
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
        ], '请使用认证器App扫描二维码');
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
            return $this->error('设置会话已过期，请重新初始化', 400);
        }

        $google2fa = new Google2FA();
        if (!$google2fa->verifyKey($secret, $request->code)) {
            return $this->error('验证码错误', 400);
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
        ], '双因素认证已启用，请妥善保存恢复码');
    }

    /**
     * 禁用双因素认证
     */
    public function disable2fa(Request $request): JsonResponse
    {
        $request->validate(['password' => 'required|string']);

        $user = $request->user();
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['password' => ['密码错误']]);
        }

        $user->update([
            'two_factor_secret' => null,
            'two_factor_enabled' => false,
        ]);

        Cache::forget("2fa_recovery:{$user->id}");

        return $this->success(null, '双因素认证已禁用');
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
                return $this->success(['user' => $user->load(['company', 'roles']), 'token' => $token], '注册成功', 201);
    }

    public function sendSmsCode(Request $request): JsonResponse
    {
        $request->validate(['mobile' => 'required|string|max:20', 'purpose' => 'required|in:login,register,reset_password']);
        $throttleKey = 'sms:'.$request->mobile;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) { return $this->error('发送过于频繁', 429); }
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        cache()->put("sms_code:{$request->mobile}:{$request->purpose}", $code, now()->addMinutes(5));
        RateLimiter::hit($throttleKey, 60);
        return $this->success(null, '验证码已发送');
    }

    public function loginWithSms(Request $request): JsonResponse
    {
        $request->validate(['mobile' => 'required|string|max:20', 'code' => 'required|string|size:6']);
        $cachedCode = cache()->get("sms_code:{$request->mobile}:login");
        if (!$cachedCode || $cachedCode !== $request->code) { return $this->error('验证码错误', 400); }
        cache()->forget("sms_code:{$request->mobile}:login");
        $user = User::where('mobile', $request->mobile)->first();
        if (!$user) return $this->error('该手机号未注册', 404);
        if ($user->status !== UserStatus::Active) return $this->error('账户已被禁用', 403);
        $user->update(['last_login' => now()]);
        Context::add('current_company_id', $user->company_id);
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success(['user' => $user->load(['company', 'roles']), 'token' => $token], '登录成功');
    }

    public function loginWithWechat(Request $request): JsonResponse { return $this->error('微信登录暂未开放', 501); }
    public function loginWithDingtalk(Request $request): JsonResponse { return $this->error('钉钉登录暂未开放', 501); }
    public function loginWithFeishu(Request $request): JsonResponse { return $this->error('飞书登录暂未开放', 501); }
    public function loginWithWework(Request $request): JsonResponse { return $this->error('企微登录暂未开放', 501); }

    /**
     * 发送邮箱验证码
     */
    public function sendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return $this->error('邮箱已验证', 400);
        }
        $throttleKey = "email_verify:{$user->id}";
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            return $this->error('发送过于频繁，请稍后再试', 429);
        }
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Cache::put("email_verify:{$user->id}", $code, now()->addMinutes(10));
        Mail::raw(
            "您的邮箱验证码是：{$code}，10分钟内有效。",
            fn($message) => $message->to($user->email)->subject('邮箱验证 - ' . config('app.name'))
        );
        RateLimiter::hit($throttleKey, 60);
        return $this->success(null, '验证邮件已发送');
    }

    /**
     * 验证邮箱
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|size:6']);
        $user = $request->user();
        $cachedCode = Cache::get("email_verify:{$user->id}");
        if (!$cachedCode || $cachedCode !== $request->code) {
            return $this->error('验证码错误', 400);
        }
        $user->markEmailAsVerified();
        Cache::forget("email_verify:{$user->id}");
        return $this->success(null, '邮箱验证成功');
    }

    /**
     * 管理员手动验证用户邮箱
     */
    public function adminVerifyEmail(Request $request, User $user): JsonResponse
    {
        if (!$request->user()->isAdmin() && !$request->user()->isSuperAdmin()) {
            return $this->error('无权操作', 403);
        }
        $user->markEmailAsVerified();
        return $this->success(null, '邮箱已手动验证');
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
                "您正在重置密码，请点击链接：\n\n{$resetUrl}\n\n此链接1小时内有效。",
                fn($message) => $message->to($user->email)->subject('密码重置 - ' . config('app.name'))
            );
        }
        return $this->success(null, '如果该邮箱已注册，重置邮件已发送');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $resetData = Cache::get("password_reset:{$validated['token']}");
        if (!$resetData) {
            return $this->error('重置链接已过期或无效', 400);
        }
        $user = User::find($resetData['user_id']);
        if (!$user) { return $this->error('用户不存在', 404); }
        $user->update(['password' => $validated['password']]);
        $user->tokens()->delete();
        Cache::forget("password_reset:{$validated['token']}");
        return $this->success(null, '密码重置成功');
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
        if (!$userAuth) { return $this->error('您不属于该公司', 403); }
        $user->update(['company_id' => $companyId]);
        Context::add('current_company_id', $companyId);
        $user->tokens()->delete();
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success([
            'user' => $user->fresh()->load(['company', 'employeeDetail', 'roles', 'permissions']),
            'token' => $token, 'token_type' => 'Bearer',
        ], '公司切换成功');
    }

    /**
     * 邀请用户注册
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
            "您已被邀请加入 {$user->company?->company_name}，请点击链接完成注册：\n\n{$inviteUrl}\n\n此链接7天内有效。",
            fn($message) => $message->to($validated['email'])->subject('加入邀请 - ' . config('app.name'))
        );
        return $this->success(['invite_token' => $inviteToken], '邀请邮件已发送');
    }

    /**
     * 通过邀请链接注册
     */
    public function registerWithInvite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invite_token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'nullable|string|max:20',
        ]);
        $inviteData = Cache::get("invite:{$validated['invite_token']}");
        if (!$inviteData) { return $this->error('邀请链接已过期或无效', 400); }
        if (User::where('email', $inviteData['email'])->exists()) {
            return $this->error('该邮箱已注册', 400);
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
        return $this->success(['user' => $user->load(['company', 'roles']), 'token' => $token], '注册成功', 201);
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
            throw ValidationException::withMessages(['current_password' => ['当前密码错误']]);
        }
                $request->user()->update(['password' => $request->password]);
        return $this->success(null, '密码修改成功');
    }

    public function logout(Request $request): JsonResponse { $request->user()->currentAccessToken()->delete(); return $this->success(null, '已退出登录'); }
    public function logoutAll(Request $request): JsonResponse { $request->user()->tokens()->delete(); return $this->success(null, '已退出所有设备'); }

    /**
     * 接受员工邀请 — 设置密码并激活账户
     */
    public function acceptEmployeeInvite(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $inviteData = Cache::get("employee_invite:{$validated['token']}");
        if (!$inviteData) {
            return $this->error('邀请链接无效或已过期', 400);
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
        ], '账户激活成功');
    }
}

