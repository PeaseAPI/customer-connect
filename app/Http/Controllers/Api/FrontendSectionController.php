<?php

namespace App\Http\Controllers\Api;

use App\Models\FrontendSection;
use Illuminate\Http\Request;

class FrontendSectionController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(FrontendSection::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(FrontendSection::create($d),'Created',201);}
    public function show(FrontendSection $m){return $this->success($m);}
    public function update(Request $r,FrontendSection $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(FrontendSection $m){$m->delete();return $this->success(null,'Deleted');}
}
