<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EnrollmentApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(private string $courseName = '') {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'content',
            'title' => 'Inscrição aprovada! 🎉',
            'body'  => 'A sua inscrição' . ($this->courseName ? ' no curso "' . $this->courseName . '"' : '') . ' foi aprovada. Bem-vindo(a) à Olsangola!',
        ];
    }
}
