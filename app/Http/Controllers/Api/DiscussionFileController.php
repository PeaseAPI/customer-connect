<?php

namespace App\Http\Controllers\Api;

use App\Models\DiscussionFile;
use Illuminate\Http\Request;

class DiscussionFileController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(DiscussionFile::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(DiscussionFile::create($d),'Created',201);}
    public function show(DiscussionFile $m){return $this->success($m);}
    public function update(Request $r,DiscussionFile $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(DiscussionFile $m){$m->delete();return $this->success(null,'Deleted');}
}
