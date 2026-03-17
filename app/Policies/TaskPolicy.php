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
        // Có thể xem Task nếu là chủ dự án HOẶC chính task này ĐƯỢC GIAO CHO MÌNH
        return $user->id === $task->project->user_id || $task->assignee_id === $user->id;
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
        // Có thể Kéo-Thả, Sửa trạng thái nếu là chủ dự án HOẶC task này ĐƯỢC GIAO CHO MÌNH
        return $user->id === $task->project->user_id || $task->assignee_id === $user->id;
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
