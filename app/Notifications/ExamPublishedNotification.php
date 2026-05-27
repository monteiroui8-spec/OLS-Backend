<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ExamPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(private Exam $exam) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'exam',
            'title'   => 'Novo exame disponível',
            'body'    => 'O exame "' . $this->exam->title . '" está agora disponível.'
                       . ($this->exam->due_date ? ' Prazo: ' . $this->exam->due_date->format('d/m/Y') . '.' : ''),
            'examId'  => $this->exam->id,
        ];
    }
}
