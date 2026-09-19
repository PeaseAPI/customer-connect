<?php

namespace App\Http\Controllers\Api;

use App\Models\ProjectRating;
use Illuminate\Http\Request;

class ProjectRatingController extends BaseApiController
{
    public function index(Request $r){return $this->paginated(ProjectRating::query()->latest(),$r);}
    public function store(Request $r){$d=$r->all();$d['company_id']=app('App\Services\ContextService')->getCompanyId();return $this->success(ProjectRating::create($d),'Created',201);}
    public function show(ProjectRating $m){return $this->success($m);}
    public function update(Request $r,ProjectRating $m){$m->update($r->all());return $this->success($m,'Updated');}
    public function destroy(ProjectRating $m){$m->delete();return $this->success(null,'Deleted');}
}
