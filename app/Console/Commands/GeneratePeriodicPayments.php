<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class GeneratePeriodicPayments extends Command
{
    protected $signature = 'payments:generate-periodic';

    protected $description = 'Generate periodic tuition payments for active enrollments and notify students';

    public function handle(): int
    {
        $enrollments = Enrollment::with(['student.user', 'course'])
            ->where('status', 'active')
            ->get();

        $count = 0;

        foreach ($enrollments as $enrollment) {
            $startDate = $enrollment->payment_start_date ? Carbon::parse($enrollment->payment_start_date) : ($enrollment->start_date ? Carbon::parse($enrollment->start_date) : null);
            
            if (!$startDate) {
                continue;
            }

            if (! $enrollment->course) {
                continue;
            }

            $freq = $enrollment->payment_frequency ?? 'monthly';
            $monthsToAdd = match($freq) {
                'quarterly' => 3,
                'semiannual' => 6,
                'annual' => 12,
                default => 1,
            };

            // Find the latest payment for this enrollment
            $latestPayment = Payment::where('enrollment_id', $enrollment->id)
                ->orderBy('due_date', 'desc')
                ->first();

            if ($latestPayment) {
                $nextDueDate = Carbon::parse($latestPayment->due_date)->addMonthsNoOverflow($monthsToAdd);
            } else {
                $nextDueDate = $startDate;
            }

            // Generate if next due date is within 7 days
            if ($nextDueDate->isPast() || $nextDueDate->diffInDays(now(), false) >= -7) {
                // Previne duplicados para a mesma data exata
                $exists = Payment::where('enrollment_id', $enrollment->id)
                    ->whereDate('due_date', $nextDueDate->toDateString())
                    ->exists();

                if ($exists) {
                    continue;
                }

                $payment = Payment::create([
                    'student_id' => $enrollment->student_id,
                    'course_id' => $enrollment->course_id,
                    'enrollment_id' => $enrollment->id,
                    'description' => 'Propina — '.$enrollment->course->title_pt.' — '. ucfirst($nextDueDate->translatedFormat('F Y')),
                    'amount' => $enrollment->course->price_aoa,
                    'currency' => 'AOA',
                    'due_date' => $nextDueDate,
                    'status' => 'pending',
                ]);

                $count++;

                // Enviar notificação de pagamento gerado ao aluno
                if ($enrollment->student && $enrollment->student->user) {
                    $user = $enrollment->student->user;
                    Mail::send('emails.payment-generated', [
                        'user' => $user,
                        'payment' => $payment,
                    ], function ($message) use ($user) {
                        $message->to($user->email, $user->full_name)
                            ->subject('Novo Pagamento a Vencer - Olsangola Corporation');
                    });
                }
            }
        }

        $this->info('Gerados '.$count.' pagamentos periódicos.');

        return self::SUCCESS;
    }
}
