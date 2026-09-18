<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use HasCrudActions;

    public function index(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'search' => $request->input('search'),
            'status' => $request->input('status'),
        ]);
        $response = $this->apiGet($request, '/api/pm/projects?' . http_build_query($params));
        $data = $response->json();
        return view('projects.index', [
            'projects' => $data['data'] ?? [],
            'pagination' => $this->extractPagination($data),
        ]);
    }

    public function store(Request $request)
    {
        $response = $this->apiPost($request, '/api/pm/projects', $request->all());
        if ($response->successful()) {
            return redirect()->route('projects.index')->with('success', 'Project created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function show(Request $request, $id)
    {
        $response = $this->apiGet($request, "/api/pm/projects/{$id}");
        return view('projects.show', [
            'project' => $response->json('data', []),
        ]);
    }

    public function update(Request $request, $id)
    {
        $response = $this->apiPut($request, "/api/pm/projects/{$id}", $request->all());
        if ($response->successful()) {
            return redirect()->route('projects.show', $id)->with('success', 'Project updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroy(Request $request, $id)
    {
        $this->apiDelete($request, "/api/pm/projects/{$id}");
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully');
    }

    public function tasks(Request $request, $projectId)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'keyword' => $request->input('search'),
            'status' => $request->input('status'),
        ]);
        $response = $this->apiGet($request, "/api/pm/projects/{$projectId}/tasks?" . http_build_query($params));
        $data = $response->json();
        return view('projects.tasks', [
            'tasks' => $data['data'] ?? [],
            'projectId' => $projectId,
            'pagination' => $this->extractPagination($data),
        ]);
    }

    public function allTasks(Request $request)
    {
        $params = array_filter([
            'page' => $request->input('page', 1),
            'keyword' => $request->input('search'),
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
        ]);
        $response = $this->apiGet($request, '/api/pm/tasks?' . http_build_query($params));
        $data = $response->json();
        return view('projects.tasks', [
            'tasks' => $data['data'] ?? [],
            'projectId' => null,
            'pagination' => $this->extractPagination($data),
        ]);
    }

    public function storeTask(Request $request)
    {
        $url = $request->project_id
            ? "/api/pm/projects/{$request->project_id}/tasks"
            : '/api/pm/tasks';
        $response = $this->apiPost($request, $url, $request->all());
        if ($response->successful()) {
            return redirect()->route('tasks.index')->with('success', 'Task created successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function updateTask(Request $request, $id)
    {
        $response = $this->apiPut($request, "/api/pm/tasks/{$id}", $request->all());
        if ($response->successful()) {
            return back()->with('success', 'Task updated successfully');
        }
        return back()->withErrors($response->json('errors', []))->withInput();
    }

    public function destroyTask(Request $request, $id)
    {
        $this->apiDelete($request, "/api/pm/tasks/{$id}");
        return back()->with('success', 'Task deleted successfully');
    }
}
