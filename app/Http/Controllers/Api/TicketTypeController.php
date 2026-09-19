<?php

namespace App\Http\Controllers\Api;

use App\Models\TicketType;
use Illuminate\Http\Request;

class TicketTypeController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(TicketType::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(TicketType::create($d),'Created',201);}
    public function show(TicketType $m){return $this->success($m);}
    public function update(Request $r,TicketType $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(TicketType $m){$m->delete();return $this->success(null,'Deleted');}
}
