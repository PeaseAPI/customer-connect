<?php

namespace App\Http\Controllers\Api;

use App\Models\LeadForm;
use Illuminate\Http\Request;

class LeadFormController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(LeadForm::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(LeadForm::create($d),'Created',201);}
    public function show(LeadForm $m){return $this->success($m);}
    public function update(Request $r,LeadForm $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(LeadForm $m){$m->delete();return $this->success(null,'Deleted');}
}
