<?php

namespace App\Http\Controllers\Api;

use App\Models\FrontendSection;
use App\Models\TestimonialSetting;
use App\Models\Faq;

class FrontPublicController extends BaseApiController
{
    public function home(){return $this->success(['sections'=>FrontendSection::where('is_active',true)->orderBy('sort_order')->get(),'testimonials'=>TestimonialSetting::all(),'faqs'=>Faq::where('status','active')->get()]);}
    public function pricing(){return $this->success(FrontendSection::where('page_section','pricing')->where('is_active',true)->get());}
    public function features(){return $this->success(FrontendSection::where('page_section','features')->where('is_active',true)->get());}
}