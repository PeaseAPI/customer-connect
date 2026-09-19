<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends BaseApiController
{
    /**
     * Global search across multiple modules.
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1|max:100',
            'modules' => 'nullable|array',
            'modules.*' => 'string|in:projects,tasks,clients,employees,leads,invoices,estimates,contracts,tickets,expenses',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $request->input('q');
        $modules = $request->input('modules', ['projects', 'tasks', 'clients', 'employees', 'leads', 'invoices', 'estimates', 'contracts', 'tickets', 'expenses']);
        $limit = $request->input('limit', 10);
        $companyId = app('App\Services\ContextService')->getCompanyId();

        $results = [];

        if (in_array('projects', $modules)) {
            $results['projects'] = DB::table('projects')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->select('id', 'name', 'status', 'end_date')
                ->limit($limit)->get();
        }

        if (in_array('tasks', $modules)) {
            $results['tasks'] = DB::table('tasks')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('title', 'LIKE', "%{$query}%")
                      ->orWhere('description', 'LIKE', "%{$query}%");
                })
                ->select('id', 'title', 'status', 'project_id')
                ->limit($limit)->get();
        }

        if (in_array('clients', $modules)) {
            $results['clients'] = DB::table('clients')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%")
                      ->orWhere('phone', 'LIKE', "%{$query}%");
                })
                ->select('id', 'name', 'email', 'status')
                ->limit($limit)->get();
        }

        if (in_array('employees', $modules)) {
            $results['employees'] = DB::table('employees')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->select('id', 'name', 'email', 'status')
                ->limit($limit)->get();
        }

        if (in_array('leads', $modules)) {
            $results['leads'] = DB::table('leads')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('company_name', 'LIKE', "%{$query}%")
                      ->orWhere('contact_name', 'LIKE', "%{$query}%")
                      ->orWhere('email', 'LIKE', "%{$query}%");
                })
                ->select('id', 'company_name', 'status', 'value')
                ->limit($limit)->get();
        }

        if (in_array('invoices', $modules)) {
            $results['invoices'] = DB::table('invoices')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('invoice_number', 'LIKE', "%{$query}%");
                })
                ->select('id', 'invoice_number', 'status', 'total')
                ->limit($limit)->get();
        }

        if (in_array('estimates', $modules)) {
            $results['estimates'] = DB::table('estimates')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('estimate_number', 'LIKE', "%{$query}%");
                })
                ->select('id', 'estimate_number', 'status', 'total')
                ->limit($limit)->get();
        }

        if (in_array('contracts', $modules)) {
            $results['contracts'] = DB::table('contracts')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('subject', 'LIKE', "%{$query}%");
                })
                ->select('id', 'subject', 'status', 'end_date')
                ->limit($limit)->get();
        }

        if (in_array('tickets', $modules)) {
            $results['tickets'] = DB::table('tickets')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('subject', 'LIKE', "%{$query}%")
                      ->orWhere('ticket_id', 'LIKE', "%{$query}%");
                })
                ->select('id', 'subject', 'status', 'priority')
                ->limit($limit)->get();
        }

        if (in_array('expenses', $modules)) {
            $results['expenses'] = DB::table('expenses')
                ->where('company_id', $companyId)
                ->where(function ($q) use ($query) {
                    $q->where('item_name', 'LIKE', "%{$query}%");
                })
                ->select('id', 'item_name', 'status', 'amount')
                ->limit($limit)->get();
        }

        return $this->success($results, '搜索结果');
    }
}
