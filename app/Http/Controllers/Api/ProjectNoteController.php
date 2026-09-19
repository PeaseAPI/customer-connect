<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectNote;
use Illuminate\Http\Request;

class ProjectNoteController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(ProjectNote::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(ProjectNote::create($d),'Created',201);}
    public function show(ProjectNote $m){return $this->success($m);}
    public function update(Request $r,ProjectNote $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(ProjectNote $m){$m->delete();return $this->success(null,'Deleted');}
}
