<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class SignUpSettingController extends BaseApiController
{
    public function show(){return $this->success(['enable_signup'=>config('app.enable_signup',true),'default_role'=>config('app.default_role','employee'),'email_verification'=>config('app.email_verification',true)]);}
    public function update(Request $r){return $this->success(null,'Updated');}
}