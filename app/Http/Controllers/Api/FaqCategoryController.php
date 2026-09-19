<?php

namespace App\Http\Controllers\Api;

use App\Models\FaqCategory;
use Illuminate\Http\Request;

class FaqCategoryController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(FaqCategory::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(FaqCategory::create($d),'Created',201);}
    public function show(FaqCategory $m){return $this->success($m);}
    public function update(Request $r,FaqCategory $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(FaqCategory $m){$m->delete();return $this->success(null,'Deleted');}
}
