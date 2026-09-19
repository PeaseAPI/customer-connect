<?php

namespace App\Http\Controllers\Api;

use App\Models\KnowledgeBaseFile;
use Illuminate\Http\Request;

class KnowledgeBaseFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(KnowledgeBaseFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(KnowledgeBaseFile::create($d),'Created',201);}
    public function show(KnowledgeBaseFile $m){return $this->success($m);}
    public function update(Request $r,KnowledgeBaseFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(KnowledgeBaseFile $m){$m->delete();return $this->success(null,'Deleted');}
}
