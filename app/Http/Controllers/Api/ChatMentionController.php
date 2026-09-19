<?php

namespace App\Http\Controllers\Api;

use App\Models\ChatMention;
use Illuminate\Http\Request;

class ChatMentionController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(ChatMention::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(ChatMention::create($d),'Created',201);}
    public function show(ChatMention $m){return $this->success($m);}
    public function update(Request $r,ChatMention $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(ChatMention $m){$m->delete();return $this->success(null,'Deleted');}
}
