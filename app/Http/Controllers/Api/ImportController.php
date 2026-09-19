<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class ImportController extends BaseApiController
{
    public function import(Request $r){
        $r->validate(['file'=>'required|file|mimes:csv,xlsx,xls','module'=>'required|string']);
        $module=$r->input('module');$file=$r->file('file');
        $svc=app('App\Services\ImportService');
        return $this->success($svc->import($module,$file),'Import queued');
    }
}