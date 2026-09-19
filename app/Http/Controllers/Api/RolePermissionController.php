<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolePermissionController extends BaseApiController
{
    public function index(){return $this->success(Role::with('permissions')->get());}
    public function store(Request $r){$role=Role::create(['name'=>$r->input('name')]);if($r->has('permissions'))$role->syncPermissions($r->input('permissions'));return $this->success($role,'Created',201);}
    public function update(Request $r,$id){$role=Role::findOrFail($id);$role->update(['name'=>$r->input('name',$role->name)]);if($r->has('permissions'))$role->syncPermissions($r->input('permissions'));return $this->success($role,'Updated');}
    public function destroy($id){Role::findOrFail($id)->delete();return $this->success(null,'Deleted');}
}