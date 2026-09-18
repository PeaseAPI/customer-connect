<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FinanceController extends Controller
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

    public function invoices(Request $request)
    {
        $response = $this->api($request, '/api/finance/invoices');
        return view('finance.invoices', [
            'invoices' => $response->json('data', []),
        ]);
    }

    public function estimates(Request $request)
    {
        $response = $this->api($request, '/api/finance/estimates');
        return view('finance.estimates', [
            'estimates' => $response->json('data', []),
        ]);
    }

    public function payments(Request $request)
    {
        $response = $this->api($request, '/api/finance/payments');
        return view('finance.payments', [
            'payments' => $response->json('data', []),
        ]);
    }

    public function expenses(Request $request)
    {
        $response = $this->api($request, '/api/finance/expenses');
        return view('finance.expenses', [
            'expenses' => $response->json('data', []),
        ]);
    }
}
