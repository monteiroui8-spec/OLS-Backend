<?php

namespace App\Notifications;

use App\Models\Grade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class GradeAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Grade $grade)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $pct = $this->grade->max_grade > 0
            ? round(($this->grade->grade / $this->grade->max_grade) * 100, 1)
            : 0;

        return [
            'type'    => 'grade',
            'title'   => 'Nova nota atribuída',
            'body'    => 'Recebeu ' . $this->grade->grade . '/' . $this->grade->max_grade
                       . ' (' . $pct . '%) em "' . $this->grade->title . '".',
            'gradeId' => $this->grade->id,
            'course'  => $this->grade->course?->getTitle('pt'),
        ];
    }
}
