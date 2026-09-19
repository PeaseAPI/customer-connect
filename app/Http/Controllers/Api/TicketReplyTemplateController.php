<?php

namespace App\Http\Controllers\Api;

use App\Models\TicketReplyTemplate;
use Illuminate\Http\Request;

class TicketReplyTemplateController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(TicketReplyTemplate::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(TicketReplyTemplate::create($d),'Created',201);}
    public function show(TicketReplyTemplate $m){return $this->success($m);}
    public function update(Request $r,TicketReplyTemplate $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(TicketReplyTemplate $m){$m->delete();return $this->success(null,'Deleted');}
}
