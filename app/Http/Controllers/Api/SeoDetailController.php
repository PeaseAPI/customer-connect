<?php

namespace App\Http\Controllers\Api;

use App\Models\SeoDetail;
use Illuminate\Http\Request;

class SeoDetailController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(SeoDetail::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(SeoDetail::create($d),'Created',201);}
    public function show(SeoDetail $m){return $this->success($m);}
    public function update(Request $r,SeoDetail $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(SeoDetail $m){$m->delete();return $this->success(null,'Deleted');}
}
