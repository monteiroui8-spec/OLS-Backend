<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(private Payment $payment, private string $reason = '') {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'      => 'payment',
            'title'     => 'Pagamento rejeitado',
            'body'      => 'O seu pagamento "' . $this->payment->description . '" foi rejeitado.'
                         . ($this->reason ? ' Motivo: ' . $this->reason : ''),
            'paymentId' => $this->payment->id,
        ];
    }
}
