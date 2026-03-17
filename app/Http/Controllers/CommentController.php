<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Lấy danh sách bình luận của một Task cụ thể.
     */
    public function index(Request $request, Task $task)
    {
        // Yêu cầu quyền xem Task trước khi xem Bình luận của Task đó
        Gate::authorize('view', $task);

        $comments = $task->comments()->with('user:id,name,email')->oldest()->get();
        return response()->json($comments);
    }

    /**
     * Tạo một bình luận mới vào Task.
     */
    public function store(Request $request, Task $task)
    {
        // Để comment được, bạn cũng phải có quyền xem cái Task đó
        Gate::authorize('view', $task);

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = $task->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        return response()->json($comment->load('user:id,name,email'), 201);
    }

    /**
     * Xóa một bình luận. (Chỉ User tạo ra bình luận đó hoặc Admin mới được xóa)
     */
    public function destroy(Request $request, Comment $comment)
    {
        // Nếu không phải là tác giả của Bình luận & không phải Admin thì chặn
        if ($comment->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}
