<?php

namespace App\Http\Controllers\Api;

use App\Models\TicketEmailSetting;
use Illuminate\Http\Request;

class TicketEmailSettingController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(TicketEmailSetting::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(TicketEmailSetting::create($d),'Created',201);}
    public function show(TicketEmailSetting $m){return $this->success($m);}
    public function update(Request $r,TicketEmailSetting $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(TicketEmailSetting $m){$m->delete();return $this->success(null,'Deleted');}
}
