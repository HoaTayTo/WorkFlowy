<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        // Admin xem được tất cả project
        if ($request->user()->isAdmin()) {
            // Load all projects with their owners for better UI display later
            return response()->json(Project::with('user')->get());
        }
        
        // Người dùng thường: Thấy Project CỦA MÌNH + Project CỦA NGƯỜI KHÁC (nhưng có giao việc cho mình)
        return response()->json(
            Project::where('user_id', $request->user()->id)
                ->orWhereHas('tasks', function ($q) use ($request) {
                    $q->where('assignee_id', $request->user()->id);
                })
                ->get()
        );
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Project::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $project = $request->user()->projects()->create($request->all());

        return response()->json($project, 201);
    }

    public function show(Request $request, Project $project)
    {
        Gate::authorize('view', $project);
        
        // Return project with tasks
        return response()->json($project->load('tasks.assignee'));
    }

    public function update(Request $request, Project $project)
    {
        Gate::authorize('update', $project);

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $project->update($request->all());

        return response()->json($project);
    }

    public function destroy(Request $request, Project $project)
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}
