<?php

namespace App\Http\Controllers\Api;

use App\Models\LeaveFile;
use Illuminate\Http\Request;

class LeaveFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(LeaveFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(LeaveFile::create($d),'Created',201);}
    public function show(LeaveFile $m){return $this->success($m);}
    public function update(Request $r,LeaveFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(LeaveFile $m){$m->delete();return $this->success(null,'Deleted');}
}
