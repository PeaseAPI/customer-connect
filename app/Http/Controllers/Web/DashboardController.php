<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $token = $user->createToken('web-session')->plainTextToken;

        // Fetch dashboard data via internal API
        $response = Http::withToken($token)
            ->withHeaders(['X-Company-Id' => $user->company_id ?? 1])
            ->get(url('/api/dashboard'));

        $data = $response->successful() ? $response->json('data') : [];

        return view('dashboard.index', [
            'user' => $user,
            'data' => $data,
            'apiToken' => $token,
        ]);
    }
}
