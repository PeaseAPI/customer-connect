<?php

namespace App\Http\Controllers\Api;

use App\Models\RecurringEvent;
use Illuminate\Http\Request;

class RecurringEventController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(RecurringEvent::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(RecurringEvent::create($d),'Created',201);}
    public function show(RecurringEvent $m){return $this->success($m);}
    public function update(Request $r,RecurringEvent $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(RecurringEvent $m){$m->delete();return $this->success(null,'Deleted');}
}
