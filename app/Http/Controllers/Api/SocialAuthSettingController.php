<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class SocialAuthSettingController extends BaseApiController
{
    public function show(){return $this->success(['google'=>['enabled'=>(bool)config('services.google.client_id')],'github'=>['enabled'=>(bool)config('services.github.client_id')]]);}
    public function update(Request $r){return $this->success(null,'Updated');}
}