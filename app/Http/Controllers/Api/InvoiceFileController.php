<?php

namespace App\Http\Controllers\Api;

use App\Models\InvoiceFile;
use Illuminate\Http\Request;

class InvoiceFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(InvoiceFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(InvoiceFile::create($d),'Created',201);}
    public function show(InvoiceFile $m){return $this->success($m);}
    public function update(Request $r,InvoiceFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(InvoiceFile $m){$m->delete();return $this->success(null,'Deleted');}
}
