<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    use HasCrudActions;

    public function leads(Request $request)
    {
        $response = $this->apiGet($request, '/api/crm/leads');
        return view('crm.leads', [
            'leads' => $response->json('data', []),
        ]);
    }

    public function storeLead(Request $request)
    {
        $response = $this->apiPost($request, '/api/crm/leads', $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.leads')->with('success', 'Lead created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateLead(Request $request, $id)
    {
        $response = $this->apiPut($request, "/api/crm/leads/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.leads')->with('success', 'Lead updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyLead(Request $request, $id)
    {
        $this->apiDelete($request, "/api/crm/leads/{$id}");
        return redirect()->route('crm.leads')->with('success', 'Lead deleted successfully');
    }

    public function convertLead(Request $request, $id)
    {
        $response = $this->apiPost($request, "/api/crm/leads/{$id}/convert");
        if ($response->successful()) {
            return redirect()->route('crm.leads')->with('success', 'Lead converted to client');
        }
        return back()->with('error', 'Failed to convert lead');
    }

    public function clients(Request $request)
    {
        $response = $this->apiGet($request, '/api/crm/clients');
        return view('crm.clients', [
            'clients' => $response->json('data', []),
        ]);
    }

    public function storeClient(Request $request)
    {
        $response = $this->apiPost($request, '/api/crm/clients', $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.clients')->with('success', 'Client created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateClient(Request $request, $id)
    {
        $response = $this->apiPut($request, "/api/crm/clients/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.clients')->with('success', 'Client updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyClient(Request $request, $id)
    {
        $this->apiDelete($request, "/api/crm/clients/{$id}");
        return redirect()->route('crm.clients')->with('success', 'Client deleted successfully');
    }

    public function deals(Request $request)
    {
        $response = $this->apiGet($request, '/api/crm/deals');
        return view('crm.deals', [
            'deals' => $response->json('data', []),
        ]);
    }

    public function storeDeal(Request $request)
    {
        $response = $this->apiPost($request, '/api/crm/deals', $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.deals')->with('success', 'Deal created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateDeal(Request $request, $id)
    {
        $response = $this->apiPut($request, "/api/crm/deals/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.deals')->with('success', 'Deal updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyDeal(Request $request, $id)
    {
        $this->apiDelete($request, "/api/crm/deals/{$id}");
        return redirect()->route('crm.deals')->with('success', 'Deal deleted successfully');
    }

    public function pipelines(Request $request)
    {
        $response = $this->apiGet($request, '/api/crm/pipelines');
        return view('crm.pipelines', [
            'pipelines' => $response->json('data', []),
        ]);
    }

    public function storePipeline(Request $request)
    {
        $response = $this->apiPost($request, '/api/crm/pipelines', $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.pipelines')->with('success', 'Pipeline created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updatePipeline(Request $request, $id)
    {
        $response = $this->apiPut($request, "/api/crm/pipelines/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.pipelines')->with('success', 'Pipeline updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyPipeline(Request $request, $id)
    {
        $this->apiDelete($request, "/api/crm/pipelines/{$id}");
        return redirect()->route('crm.pipelines')->with('success', 'Pipeline deleted successfully');
    }
}
