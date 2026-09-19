<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    use HasCrudActions;

    public function leads(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
            'status_id' => $request->input('status_id'),
        ]);
        $response = $this->apiGet($request, '/api/crm/leads?' . http_build_query($params));
        $data = $response->json();
        $stages = $this->apiGet($request, '/api/crm/lead-stages')->json('data', []);
        return view('crm.leads', [
            'leads' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
            'leadStages' => $stages,
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

    public function updateLead(Request $request, int $id)
    {
        $response = $this->apiPut($request, "/api/crm/leads/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.leads')->with('success', 'Lead updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyLead(Request $request, int $id)
    {
        $this->apiDelete($request, "/api/crm/leads/{$id}");
        return redirect()->route('crm.leads')->with('success', 'Lead deleted successfully');
    }

    public function convertLead(Request $request, int $id)
    {
        $response = $this->apiPost($request, "/api/crm/leads/{$id}/convert");
        if ($response->successful()) {
            return redirect()->route('crm.leads')->with('success', 'Lead converted to client');
        }
        return back()->with('error', 'Failed to convert lead');
    }

    public function clients(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
        ]);
        $response = $this->apiGet($request, '/api/crm/clients?' . http_build_query($params));
        $data = $response->json();
        return view('crm.clients', [
            'clients' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
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

    public function updateClient(Request $request, int $id)
    {
        $response = $this->apiPut($request, "/api/crm/clients/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.clients')->with('success', 'Client updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyClient(Request $request, int $id)
    {
        $this->apiDelete($request, "/api/crm/clients/{$id}");
        return redirect()->route('crm.clients')->with('success', 'Client deleted successfully');
    }

    public function deals(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
            'pipeline_stage_id' => $request->input('pipeline_stage_id'),
        ]);
        $response = $this->apiGet($request, '/api/crm/deals?' . http_build_query($params));
        $data = $response->json();
        $pipelines = $this->apiGet($request, '/api/crm/pipelines/all')->json('data', []);
        return view('crm.deals', [
            'deals' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
            'pipelines' => $pipelines,
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

    public function updateDeal(Request $request, int $id)
    {
        $response = $this->apiPut($request, "/api/crm/deals/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.deals')->with('success', 'Deal updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyDeal(Request $request, int $id)
    {
        $this->apiDelete($request, "/api/crm/deals/{$id}");
        return redirect()->route('crm.deals')->with('success', 'Deal deleted successfully');
    }

    public function pipelines(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
        ]);
        $response = $this->apiGet($request, '/api/crm/pipelines?' . http_build_query($params));
        $data = $response->json();
        return view('crm.pipelines', [
            'pipelines' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
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

    public function updatePipeline(Request $request, int $id)
    {
        $response = $this->apiPut($request, "/api/crm/pipelines/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('crm.pipelines')->with('success', 'Pipeline updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyPipeline(Request $request, int $id)
    {
        $this->apiDelete($request, "/api/crm/pipelines/{$id}");
        return redirect()->route('crm.pipelines')->with('success', 'Pipeline deleted successfully');
    }
}
