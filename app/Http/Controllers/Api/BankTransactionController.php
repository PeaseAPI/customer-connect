<?php

namespace App\Http\Controllers\Api;

use App\Models\BankTransaction;
use Illuminate\Http\Request;

class BankTransactionController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(BankTransaction::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(BankTransaction::create($d),'Created',201);}
    public function show(BankTransaction $m){return $this->success($m);}
    public function update(Request $r,BankTransaction $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(BankTransaction $m){$m->delete();return $this->success(null,'Deleted');}
}
