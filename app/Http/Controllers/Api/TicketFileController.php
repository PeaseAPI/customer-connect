<?php

namespace App\Http\Controllers\Api;

use App\Models\TicketFile;
use Illuminate\Http\Request;

class TicketFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(TicketFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(TicketFile::create($d),'Created',201);}
    public function show(TicketFile $m){return $this->success($m);}
    public function update(Request $r,TicketFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(TicketFile $m){$m->delete();return $this->success(null,'Deleted');}
}
