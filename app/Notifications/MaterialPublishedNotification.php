<?php

namespace App\Notifications;

use App\Models\Material;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MaterialPublishedNotification extends Notification
{
    use Queueable;

    public function __construct(private Material $material) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'content',
            'title'      => 'Novo material disponível',
            'body'       => 'O material "' . $this->material->title . '" foi publicado'
                          . ($this->material->classGroup ? ' para a turma ' . $this->material->classGroup->name : '') . '.',
            'materialId' => $this->material->id,
        ];
    }
}
