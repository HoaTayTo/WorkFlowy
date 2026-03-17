<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskStatusUpdated extends Notification
{
    use Queueable;

    public $task;
    public $updater;
    public $newStatus;

    public function __construct(Task $task, User $updater, string $newStatus)
    {
        $this->task = $task;
        $this->updater = $updater;
        $this->newStatus = $newStatus;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusNames = [
            'todo' => 'TO DO',
            'in_progress' => 'IN PROGRESS',
            'review' => 'REVIEW',
            'done' => 'DONE'
        ];

        $statusStr = $statusNames[$this->newStatus] ?? $this->newStatus;

        return [
            'task_id' => $this->task->id,
            'title' => 'Cập nhật tiến độ dự án',
            'message' => "{$this->updater->name} đã kéo công việc \"{$this->task->title}\" sang cột {$statusStr}",
            'url' => "/projects/{$this->task->project_id}",
            'type' => 'status_updated',
        ];
    }
}
