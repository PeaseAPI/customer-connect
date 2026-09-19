<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HrmController extends Controller
{
    use HasCrudActions;

    public function employees(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
            'department_id' => $request->input('department_id'),
            'status' => $request->input('status'),
        ]);
        $response = $this->apiGet($request, '/api/hrm/employees?' . http_build_query($params));
        $data = $response->json();
        $departments = $this->apiGet($request, '/api/hrm/departments?per_page=100')->json('data', []);
        $designations = $this->apiGet($request, '/api/hrm/designations?per_page=100')->json('data', []);
        return view('hrm.employees', [
            'employees' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
            'departments' => $departments,
            'designations' => $designations,
        ]);
    }

    public function storeEmployee(Request $request)
    {
        $response = $this->apiPost($request, '/api/hrm/employees', $request->all());
        if ($response->successful()) {
            return redirect()->route('hrm.employees')->with('success', 'Employee created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateEmployee(Request $request, int $id)
    {
        $response = $this->apiPut($request, "/api/hrm/employees/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('hrm.employees')->with('success', 'Employee updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyEmployee(Request $request, int $id)
    {
        $this->apiDelete($request, "/api/hrm/employees/{$id}");
        return redirect()->route('hrm.employees')->with('success', 'Employee deleted successfully');
    }

    public function attendance(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
            'date' => $request->input('date'),
        ]);
        $response = $this->apiGet($request, '/api/hrm/attendances?' . http_build_query($params));
        $data = $response->json();
        return view('hrm.attendance', [
            'attendance' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
        ]);
    }

    public function leaves(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ]);
        $response = $this->apiGet($request, '/api/hrm/leaves?' . http_build_query($params));
        $data = $response->json();
        $leaveTypes = $this->apiGet($request, '/api/hrm/leave-types?per_page=100')->json('data', []);
        return view('hrm.leaves', [
            'leaves' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
            'leaveTypes' => $leaveTypes,
        ]);
    }

    public function storeLeave(Request $request)
    {
        $response = $this->apiPost($request, '/api/hrm/leaves', $request->all());
        if ($response->successful()) {
            return redirect()->route('hrm.leaves')->with('success', 'Leave request created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateLeave(Request $request, int $id)
    {
        $response = $this->apiPut($request, "/api/hrm/leaves/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('hrm.leaves')->with('success', 'Leave request updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyLeave(Request $request, int $id)
    {
        $this->apiDelete($request, "/api/hrm/leaves/{$id}");
        return redirect()->route('hrm.leaves')->with('success', 'Leave request deleted successfully');
    }
}
