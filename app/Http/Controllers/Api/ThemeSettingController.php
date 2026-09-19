<?php

namespace App\Http\Controllers\Api;

use App\Models\OrganisationSetting;
use Illuminate\Http\Request;

class ThemeSettingController extends BaseApiController
{
    public function show(){return $this->success(OrganisationSetting::first()?->only(['primary_color','sidebar_color','layout_type','dark_mode']));}
    public function update(Request $r){$s=OrganisationSetting::first();$s->update($r->only(['primary_color','sidebar_color','layout_type','dark_mode']));return $this->success($s,'Updated');}
}