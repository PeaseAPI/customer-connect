<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class SecuritySettingController extends BaseApiController
{
    public function show(){return $this->success(['2fa_enabled'=>config('auth.2fa_enabled',false),'password_policy'=>config('auth.password_policy','standard'),'ip_whitelist'=>config('auth.ip_whitelist',[])]);}
    public function update(Request $r){return $this->success(null,'Updated');}
}