<?php

namespace App\Http\Controllers\Api;

use App\Models\TicketChannel;
use Illuminate\Http\Request;

class TicketChannelController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(TicketChannel::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(TicketChannel::create($d),'Created',201);}
    public function show(TicketChannel $m){return $this->success($m);}
    public function update(Request $r,TicketChannel $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(TicketChannel $m){$m->delete();return $this->success(null,'Deleted');}
}
