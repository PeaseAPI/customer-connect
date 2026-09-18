<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use HasCrudActions;

    public function index(Request $request)
    {
        $response = $this->apiGet($request, '/api/pm/projects');
        return view('projects.index', [
            'projects' => $response->json('data', []),
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
        $response = $this->apiGet($request, "/api/pm/projects/{$projectId}/tasks");
        return view('projects.tasks', [
            'tasks' => $response->json('data', []),
            'projectId' => $projectId,
        ]);
    }

    public function allTasks(Request $request)
    {
        $response = $this->apiGet($request, '/api/pm/tasks');
        return view('projects.tasks', [
            'tasks' => $response->json('data', []),
            'projectId' => null,
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
