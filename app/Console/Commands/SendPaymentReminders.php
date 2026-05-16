<?php

namespace App\Console\Commands;

use App\Models\Payment;
use App\Notifications\PaymentDueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendPaymentReminders extends Command
{
    protected $signature = 'payments:send-reminders';

    protected $description = 'Send reminders for upcoming and overdue payments';

    public function handle(): int
    {
        $today = Carbon::today();

        $payments = Payment::whereIn('status', ['pending', 'overdue'])
            ->whereDate('due_date', '<=', $today->copy()->addDays(3))
            ->with('student.user')
            ->get();

        $count = 0;

        foreach ($payments as $payment) {
            if (! $payment->student || ! $payment->student->user) {
                continue;
            }

            $payment->student->user->notify(new PaymentDueNotification($payment));
            $count++;
        }

        $this->info('Enviados '.$count.' lembretes de pagamento.');

        return self::SUCCESS;
    }
}

