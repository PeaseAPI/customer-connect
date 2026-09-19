<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatMessageFile;
use Illuminate\Http\Request;

class ChatMessageFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(ChatMessageFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(ChatMessageFile::create($d),'Created',201);}
    public function show(ChatMessageFile $m){return $this->success($m);}
    public function update(Request $r,ChatMessageFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(ChatMessageFile $m){$m->delete();return $this->success(null,'Deleted');}
}
