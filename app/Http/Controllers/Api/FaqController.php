<?php

namespace App\Http\Controllers\Api;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(Faq::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(Faq::create($d),'Created',201);}
    public function show(Faq $m){return $this->success($m);}
    public function update(Request $r,Faq $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(Faq $m){$m->delete();return $this->success(null,'Deleted');}
}
