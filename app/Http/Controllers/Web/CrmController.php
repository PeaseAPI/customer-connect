<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CrmController extends Controller
{
    protected function getApiToken(Request $request): string
    {
        return $request->user()->createToken('web-session')->plainTextToken;
    }

    protected function apiHeaders(Request $request): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->getApiToken($request),
            'X-Company-Id' => $request->user()->company_id ?? 1,
            'Accept' => 'application/json',
        ];
    }

    public function leads(Request $request)
    {
        $response = Http::withHeaders($this->apiHeaders($request))
            ->get(url('/api/crm/leads'));
        return view('crm.leads', [
            'leads' => $response->json('data', []),
            'apiToken' => $this->getApiToken($request),
        ]);
    }

    public function clients(Request $request)
    {
        $response = Http::withHeaders($this->apiHeaders($request))
            ->get(url('/api/crm/clients'));
        return view('crm.clients', [
            'clients' => $response->json('data', []),
            'apiToken' => $this->getApiToken($request),
        ]);
    }

    public function deals(Request $request)
    {
        $response = Http::withHeaders($this->apiHeaders($request))
            ->get(url('/api/crm/deals'));
        return view('crm.deals', [
            'deals' => $response->json('data', []),
            'apiToken' => $this->getApiToken($request),
        ]);
    }

    public function pipelines(Request $request)
    {
        $response = Http::withHeaders($this->apiHeaders($request))
            ->get(url('/api/crm/pipelines'));
        return view('crm.pipelines', [
            'pipelines' => $response->json('data', []),
            'apiToken' => $this->getApiToken($request),
        ]);
    }
}
