<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class PaymentDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Payment $payment)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment',
            'title' => 'Mensalidade pendente',
            'body' => 'Pagamento '.$this->payment->description.' vence em '.$this->payment->due_date->format('d/m/Y'),
            'paymentId' => $this->payment->id,
        ];
    }
}

