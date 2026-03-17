<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TaskPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        // Có thể xem Task nếu là chủ dự án HOẶC có tham gia dự án (được giao ít nhất 1 task)
        return $user->id === $task->project->user_id || $task->project->tasks()->where('assignee_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; 
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        // Có thể Kéo-Thả, Sửa trạng thái nếu là chủ dự án HOẶC có tham gia dự án
        return $user->id === $task->project->user_id || $task->project->tasks()->where('assignee_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Chỉ chủ dự án gốc mới có quyền xóa bỏ vĩnh viễn thẻ tính năng này.
        return $user->id === $task->project->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Task $task): bool
    {
        return $user->id === $task->project->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Task $task): bool
    {
        return $user->id === $task->project->user_id;
    }
}
