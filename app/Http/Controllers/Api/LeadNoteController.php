<?php

namespace App\Http\Controllers\Api;

use App\Models\LeadNote;
use Illuminate\Http\Request;

class LeadNoteController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(LeadNote::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(LeadNote::create($d),'Created',201);}
    public function show(LeadNote $m){return $this->success($m);}
    public function update(Request $r,LeadNote $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(LeadNote $m){$m->delete();return $this->success(null,'Deleted');}
}
