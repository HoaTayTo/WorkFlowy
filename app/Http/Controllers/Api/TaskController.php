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
            $query->whereHas('project', function ($q) use ($request) {
                // Lấy Task nếu thuộc Project của mình
                $q->where('user_id', $request->user()->id)
                  // Hoặc thuộc Project mà mình ĐƯỢC MỜI tham gia (có chứa ít nhất 1 task gán cho mình)
                  ->orWhereHas('tasks', function ($tq) use ($request) {
                      $tq->where('assignee_id', $request->user()->id);
                  });
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
        
        // Nếu nhìn thấy bảng Kanban của project thì có quyền tạo thẻ công việc
        Gate::authorize('view', $project);

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
