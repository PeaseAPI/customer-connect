<?php

namespace App\Http\Controllers\Api;

use App\Models\EventFile;
use Illuminate\Http\Request;

class EventFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(EventFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(EventFile::create($d),'Created',201);}
    public function show(EventFile $m){return $this->success($m);}
    public function update(Request $r,EventFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(EventFile $m){$m->delete();return $this->success(null,'Deleted');}
}
