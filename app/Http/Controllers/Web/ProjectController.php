<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProjectController extends Controller
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

    public function index(Request $request)
    {
        $response = $this->api($request, '/api/pm/projects');
        return view('projects.index', [
            'projects' => $response->json('data', []),
        ]);
    }

    public function show(Request $request, $id)
    {
        $response = $this->api($request, "/api/pm/projects/{$id}");
        return view('projects.show', [
            'project' => $response->json('data', []),
        ]);
    }

    public function tasks(Request $request, $projectId)
    {
        $response = $this->api($request, "/api/pm/projects/{$projectId}/tasks");
        return view('projects.tasks', [
            'tasks' => $response->json('data', []),
            'projectId' => $projectId,
        ]);
    }

    public function allTasks(Request $request)
    {
        $response = $this->api($request, '/api/pm/tasks');
        return view('projects.tasks', [
            'tasks' => $response->json('data', []),
            'projectId' => null,
        ]);
    }
}
