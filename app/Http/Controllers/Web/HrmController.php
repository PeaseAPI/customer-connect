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
            'department' => $request->input('department'),
            'status' => $request->input('status'),
        ]);
        $response = $this->apiGet($request, '/api/hrm/employees?' . http_build_query($params));
        $data = $response->json();
        return view('hrm.employees', [
            'employees' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
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

    public function updateEmployee(Request $request, $id)
    {
        $response = $this->apiPut($request, "/api/hrm/employees/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('hrm.employees')->with('success', 'Employee updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyEmployee(Request $request, $id)
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

    public function storeAttendance(Request $request)
    {
        $response = $this->apiPost($request, '/api/hrm/attendances', $request->all());
        if ($response->successful()) {
            return redirect()->route('hrm.attendance')->with('success', 'Attendance record created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateAttendance(Request $request, $id)
    {
        $response = $this->apiPut($request, "/api/hrm/attendances/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('hrm.attendance')->with('success', 'Attendance record updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyAttendance(Request $request, $id)
    {
        $this->apiDelete($request, "/api/hrm/attendances/{$id}");
        return redirect()->route('hrm.attendance')->with('success', 'Attendance record deleted successfully');
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
        return view('hrm.leaves', [
            'leaves' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
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

    public function updateLeave(Request $request, $id)
    {
        $response = $this->apiPut($request, "/api/hrm/leaves/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('hrm.leaves')->with('success', 'Leave request updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyLeave(Request $request, $id)
    {
        $this->apiDelete($request, "/api/hrm/leaves/{$id}");
        return redirect()->route('hrm.leaves')->with('success', 'Leave request deleted successfully');
    }
}
