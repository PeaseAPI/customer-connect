<?php

namespace App\Http\Controllers\Api;

use App\Models\ProposalTemplate;
use Illuminate\Http\Request;

class ProposalTemplateController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(ProposalTemplate::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(ProposalTemplate::create($d),'Created',201);}
    public function show(ProposalTemplate $m){return $this->success($m);}
    public function update(Request $r,ProposalTemplate $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(ProposalTemplate $m){$m->delete();return $this->success(null,'Deleted');}
}
