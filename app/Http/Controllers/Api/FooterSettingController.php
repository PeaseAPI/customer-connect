<?php

namespace App\Http\Controllers\Api;

use App\Models\FooterSetting;
use Illuminate\Http\Request;

class FooterSettingController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(FooterSetting::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(FooterSetting::create($d),'Created',201);}
    public function show(FooterSetting $m){return $this->success($m);}
    public function update(Request $r,FooterSetting $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(FooterSetting $m){$m->delete();return $this->success(null,'Deleted');}
}
