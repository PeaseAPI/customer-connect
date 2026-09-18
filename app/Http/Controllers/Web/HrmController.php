<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HrmController extends Controller
{
    protected function api(Request $request, string $url)
    {
        $token = $request->user()->createToken('web-session')->plainTextToken;
        return Http::withToken($token)
            ->withHeaders([
                'X-Company-Id' => $request->user()->company_id ?? 1,
                'Accept' => 'application/json',
            ])
            ->get(url($url));
    }

    public function employees(Request $request)
    {
        $response = $this->api($request, '/api/hrm/employees');
        return view('hrm.employees', [
            'employees' => $response->json('data', []),
        ]);
    }

    public function attendance(Request $request)
    {
        $response = $this->api($request, '/api/hrm/attendance');
        return view('hrm.attendance', [
            'attendance' => $response->json('data', []),
        ]);
    }

    public function leaves(Request $request)
    {
        $response = $this->api($request, '/api/hrm/leaves');
        return view('hrm.leaves', [
            'leaves' => $response->json('data', []),
        ]);
    }
}
