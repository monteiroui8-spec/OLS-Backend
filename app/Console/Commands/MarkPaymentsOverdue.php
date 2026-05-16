<?php

namespace App\Console\Commands;

use App\Models\Payment;
use Illuminate\Console\Command;

class MarkPaymentsOverdue extends Command
{
    protected $signature = 'payments:mark-overdue';

    protected $description = 'Mark pending payments past due date as overdue';

    public function handle(): int
    {
        $updated = Payment::overdue()->update(['status' => 'overdue']);

        $this->info('Marcados '.$updated.' pagamentos como overdue.');

        return self::SUCCESS;
    }
}

