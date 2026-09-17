<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        RateLimiter::clear($throttleKey);
        $user->update(['last_login' => now()]);
        Context::add('current_company_id', $user->company_id);
        $token = $user->createToken('auth-token')->plainTextToken;
        return $this->success(['user' => $user->load(['company', 'employeeDetail', 'roles', 'permissions']), 'token' => $token, 'token_type' => 'Bearer'], '登录成功');
    }

    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:191', 'company_email' => 'required|email|max:191',
            'name' => 'required|string|max:191', 'email' => 'required|email|unique:users,email',
            'mobile' => 'nullable|string|max:20|unique:users,mobile', 'password' => 'required|string|min:8|confirmed',
        ]);
        $company = \App\Models\Company::create(['company_name' => $validated['company_name'], 'company_email' => $validated['company_email'], 'status' => \App\Enums\CompanyStatus::Active]);
        $user = User::create(['company_id' => $company->id, 'name' => $validated['name'], 'email' => $validated['email'], 'mobile' => $validated['mobile'] ?? null, 'password' => Hash::make($validated['password']), 'status' => UserStatus::Active]);
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
    public function forgotPassword(Request $request): JsonResponse { $request->validate(['email' => 'required|email']); return $this->success(null, '如果该邮箱已注册，重置邮件已发送'); }
    public function resetPassword(Request $request): JsonResponse { return $this->success(null, '密码重置成功'); }

    public function profile(Request $request): JsonResponse
    {
        return $this->success($request->user()->load(['company', 'employeeDetail.department', 'employeeDetail.designation', 'clientDetail', 'roles.permissions']));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $validated = $request->validate(['name' => 'sometimes|string|max:191', 'mobile' => 'sometimes|string|max:20|unique:users,mobile,'.$request->user()->id, 'gender' => 'sometimes|in:male,female,other']);
        $request->user()->update($validated);
        return $this->success($request->user()->fresh(), '更新成功');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate(['current_password' => 'required|string', 'password' => 'required|string|min:8|confirmed']);
        if (!Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages(['current_password' => ['当前密码错误']]);
        }
        $request->user()->update(['password' => Hash::make($request->password)]);
        return $this->success(null, '密码修改成功');
    }

    public function logout(Request $request): JsonResponse { $request->user()->currentAccessToken()->delete(); return $this->success(null, '已退出登录'); }
    public function logoutAll(Request $request): JsonResponse { $request->user()->tokens()->delete(); return $this->success(null, '已退出所有设备'); }
}

