<?php

namespace App\Http\Controllers\Api;

use App\Models\FrontendSection;
use App\Models\Faq;
use App\Models\SeoDetail;
use Illuminate\Http\Request;

class FrontendController extends BaseApiController
{
    public function sections(Request $r){return $this->paginated(FrontendSection::query()->orderBy('sort_order'),$r);}
    public function updateSection(Request $r,$id){$s=FrontendSection::findOrFail($id);$s->update($r->all());return $this->success($s,'Updated');}
    public function faqPublic(){return $this->success(Faq::where('status','active')->get());}
    public function seoPublic(){return $this->success(SeoDetail::first());}
}