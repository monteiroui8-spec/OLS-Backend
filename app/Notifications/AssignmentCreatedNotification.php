<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AssignmentCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Assignment $assignment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'         => 'exam',
            'title'        => 'Nova tarefa atribuída',
            'body'         => 'A tarefa "' . $this->assignment->title . '" foi atribuída.'
                            . ($this->assignment->due_date
                                ? ' Prazo: ' . $this->assignment->due_date->format('d/m/Y') . '.'
                                : ''),
            'assignmentId' => $this->assignment->id,
        ];
    }
}
