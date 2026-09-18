<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use HasCrudActions;

    public function index()
    {
        return view('settings.index');
    }

    public function company(Request $request)
    {
        $response = $this->apiGet($request, '/api/companies/1');
        return view('settings.company', [
            'company' => $response->json('data', []),
        ]);
    }

    public function updateCompany(Request $request)
    {
        $response = $this->apiPut($request, '/api/companies/1', $request->all());
        if ($response->successful()) {
            return redirect()->route('settings.company')->with('success', 'Company settings updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function notifications()
    {
        return view('settings.notifications');
    }
}
