<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskAssigned extends Notification
{
    use Queueable;

    public $task;
    public $assigner;

    public function __construct(Task $task, User $assigner)
    {
        $this->task = $task;
        $this->assigner = $assigner;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'title' => 'Bạn được giao việc mới',
            'message' => "{$this->assigner->name} đã giao cho bạn thẻ công việc: {$this->task->title}",
            'url' => "/projects/{$this->task->project_id}",
            'type' => 'assigned',
        ];
    }
}
