<?php

namespace App\Http\Controllers\Api;

use App\Models\LeadFile;
use Illuminate\Http\Request;

class LeadFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(LeadFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(LeadFile::create($d),'Created',201);}
    public function show(LeadFile $m){return $this->success($m);}
    public function update(Request $r,LeadFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(LeadFile $m){$m->delete();return $this->success(null,'Deleted');}
}
