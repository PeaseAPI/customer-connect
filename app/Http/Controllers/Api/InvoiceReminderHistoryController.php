<?php

namespace App\Http\Controllers\Api;

use App\Models\InvoiceReminderHistory;
use Illuminate\Http\Request;

class InvoiceReminderHistoryController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(InvoiceReminderHistory::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(InvoiceReminderHistory::create($d),'Created',201);}
    public function show(InvoiceReminderHistory $m){return $this->success($m);}
    public function update(Request $r,InvoiceReminderHistory $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(InvoiceReminderHistory $m){$m->delete();return $this->success(null,'Deleted');}
}
