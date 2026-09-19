<?php

namespace App\Http\Controllers\Api;

use App\Models\TicketSetting;
use Illuminate\Http\Request;

class TicketSettingController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(TicketSetting::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(TicketSetting::create($d),'Created',201);}
    public function show(TicketSetting $m){return $this->success($m);}
    public function update(Request $r,TicketSetting $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(TicketSetting $m){$m->delete();return $this->success(null,'Deleted');}
}
