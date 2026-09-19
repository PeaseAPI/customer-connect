<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    use HasCrudActions;

    public function invoices(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ]);
        $response = $this->apiGet($request, '/api/finance/invoices?' . http_build_query($params));
        $data = $response->json();
        $clients = $this->apiGet($request, '/api/crm/clients?per_page=100')->json('data', []);
        return view('finance.invoices', [
            'invoices' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
            'clients' => $clients,
        ]);
    }

    public function storeInvoice(Request $request)
    {
        $response = $this->apiPost($request, '/api/finance/invoices', $request->all());
        if ($response->successful()) {
            return redirect()->route('finance.invoices')->with('success', 'Invoice created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateInvoice(Request $request, string $id)
    {
        $response = $this->apiPut($request, "/api/finance/invoices/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('finance.invoices')->with('success', 'Invoice updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyInvoice(Request $request, string $id)
    {
        $this->apiDelete($request, "/api/finance/invoices/{$id}");
        return redirect()->route('finance.invoices')->with('success', 'Invoice deleted successfully');
    }

    public function estimates(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ]);
        $response = $this->apiGet($request, '/api/finance/estimates?' . http_build_query($params));
        $data = $response->json();
        $clients = $this->apiGet($request, '/api/crm/clients?per_page=100')->json('data', []);
        return view('finance.estimates', [
            'estimates' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
            'clients' => $clients,
        ]);
    }

    public function storeEstimate(Request $request)
    {
        $response = $this->apiPost($request, '/api/finance/estimates', $request->all());
        if ($response->successful()) {
            return redirect()->route('finance.estimates')->with('success', 'Estimate created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateEstimate(Request $request, string $id)
    {
        $response = $this->apiPut($request, "/api/finance/estimates/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('finance.estimates')->with('success', 'Estimate updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyEstimate(Request $request, string $id)
    {
        $this->apiDelete($request, "/api/finance/estimates/{$id}");
        return redirect()->route('finance.estimates')->with('success', 'Estimate deleted successfully');
    }

    public function payments(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
        ]);
        $response = $this->apiGet($request, '/api/finance/payments?' . http_build_query($params));
        $data = $response->json();
        $clients = $this->apiGet($request, '/api/crm/clients?per_page=100')->json('data', []);
        $invoices = $this->apiGet($request, '/api/finance/invoices?per_page=100')->json('data', []);
        return view('finance.payments', [
            'payments' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
            'clients' => $clients,
            'invoices' => $invoices,
        ]);
    }

    public function storePayment(Request $request)
    {
        $response = $this->apiPost($request, '/api/finance/payments', $request->all());
        if ($response->successful()) {
            return redirect()->route('finance.payments')->with('success', 'Payment recorded successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updatePayment(Request $request, string $id)
    {
        $response = $this->apiPut($request, "/api/finance/payments/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('finance.payments')->with('success', 'Payment updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function expenses(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
            'status' => $request->input('status'),
            'category_id' => $request->input('category_id'),
        ]);
        $response = $this->apiGet($request, '/api/finance/expenses?' . http_build_query($params));
        $data = $response->json();
        $categories = $this->apiGet($request, '/api/finance/expense-categories?per_page=100')->json('data', []);
        return view('finance.expenses', [
            'expenses' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
            'expenseCategories' => $categories,
        ]);
    }

    public function storeExpense(Request $request)
    {
        $response = $this->apiPost($request, '/api/finance/expenses', $request->all());
        if ($response->successful()) {
            return redirect()->route('finance.expenses')->with('success', 'Expense created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateExpense(Request $request, string $id)
    {
        $response = $this->apiPut($request, "/api/finance/expenses/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('finance.expenses')->with('success', 'Expense updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyExpense(Request $request, string $id)
    {
        $this->apiDelete($request, "/api/finance/expenses/{$id}");
        return redirect()->route('finance.expenses')->with('success', 'Expense deleted successfully');
    }

    public function approveExpense(Request $request, string $id)
    {
        $response = $this->apiPost($request, "/api/finance/expenses/{$id}/approve");
        if ($response->successful()) {
            return back()->with('success', 'Expense approved');
        }
        return back()->with('error', 'Failed to approve expense');
    }
}
