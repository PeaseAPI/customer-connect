<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectFile;
use Illuminate\Http\Request;

class ProjectFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(ProjectFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(ProjectFile::create($d),'Created',201);}
    public function show(ProjectFile $m){return $this->success($m);}
    public function update(Request $r,ProjectFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(ProjectFile $m){$m->delete();return $this->success(null,'Deleted');}
}
