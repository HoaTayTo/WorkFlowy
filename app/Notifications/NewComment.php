<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewComment extends Notification implements ShouldQueue
{
    use Queueable;

    public $task;
    public $commenter;
    public $commentText;

    public function __construct(Task $task, User $commenter, string $commentText)
    {
        $this->task = $task;
        $this->commenter = $commenter;
        $this->commentText = $commentText;
    }

    public function via(object $notifiable): array
    {
        // Gửi qua database và đường truyền websocket broadcast (Reverb)
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => 'Bình luận mới',
            'message' => "{$this->commenter->name} đã bình luận vào công việc: {$this->task->title}",
            'url' => "/projects/{$this->task->project_id}",
            'type' => 'new_comment',
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'data' => $this->toArray($notifiable),
            'id' => $this->id,
            'created_at' => now()->toISOString()
        ]);
    }
}
