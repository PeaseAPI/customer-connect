<?php

namespace App\Http\Controllers\Api;

use App\Models\TestimonialSetting;
use Illuminate\Http\Request;

class TestimonialSettingController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(TestimonialSetting::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(TestimonialSetting::create($d),'Created',201);}
    public function show(TestimonialSetting $m){return $this->success($m);}
    public function update(Request $r,TestimonialSetting $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(TestimonialSetting $m){$m->delete();return $this->success(null,'Deleted');}
}
