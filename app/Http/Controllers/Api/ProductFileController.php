<?php

namespace App\Http\Controllers\Api;

use App\Models\ProductFile;
use Illuminate\Http\Request;

class ProductFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(ProductFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(ProductFile::create($d),'Created',201);}
    public function show(ProductFile $m){return $this->success($m);}
    public function update(Request $r,ProductFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(ProductFile $m){$m->delete();return $this->success(null,'Deleted');}
}
