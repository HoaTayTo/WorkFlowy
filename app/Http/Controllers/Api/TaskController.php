<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        // Nếu KHÔNG PHẢI admin
        if (!$request->user()->isAdmin()) {
            $query->where(function ($q) use ($request) {
                // Thấy TẤT CẢ task nếu mình là chủ dự án đó
                $q->whereHas('project', function ($pq) use ($request) {
                    $pq->where('user_id', $request->user()->id);
                })
                // HOẶC chỉ thấy những task MÌNH ĐƯỢC GIAO
                ->orWhere('assignee_id', $request->user()->id);
            });
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return response()->json($query->with('assignee')->get());
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Task::class);

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,review,done',
            'priority' => 'nullable|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id'
        ]);

        $project = Project::findOrFail($request->project_id);
        
        // Phải là chủ dự án (hoặc Admin có Gate before) mới được tạo thẻ công việc mới
        Gate::authorize('update', $project);

        $task = Task::create($request->all());

        return response()->json($task, 201);
    }

    public function show(Request $request, Task $task)
    {
        Gate::authorize('view', $task);

        return response()->json($task->load('assignee'));
    }

    public function update(Request $request, Task $task)
    {
        Gate::authorize('update', $task);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,in_progress,review,done',
            'priority' => 'sometimes|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id'
        ]);

        $task->update($request->all());

        return response()->json($task);
    }

    public function destroy(Request $request, Task $task)
    {
        Gate::authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
