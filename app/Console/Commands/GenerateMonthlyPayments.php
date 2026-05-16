<?php

namespace App\Console\Commands;

use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateMonthlyPayments extends Command
{
    protected $signature = 'payments:generate-monthly {--month=}';

    protected $description = 'Generate monthly tuition payments for active enrollments';

    public function handle(): int
    {
        $month = $this->option('month') ? (int) $this->option('month') : now()->month;
        $year = now()->year;
        $monthName = Carbon::create($year, $month)->translatedFormat('F Y');

        $enrollments = Enrollment::with(['student', 'course'])
            ->where('status', 'active')
            ->get();

        $count = 0;

        foreach ($enrollments as $enrollment) {
            $exists = Payment::where('student_id', $enrollment->student_id)
                ->where('enrollment_id', $enrollment->id)
                ->whereMonth('due_date', $month)
                ->whereYear('due_date', $year)
                ->exists();

            if ($exists) {
                continue;
            }

            if (! $enrollment->course) {
                continue;
            }

            Payment::create([
                'student_id' => $enrollment->student_id,
                'course_id' => $enrollment->course_id,
                'enrollment_id' => $enrollment->id,
                'description' => 'Mensalidade — '.$enrollment->course->title_pt.' — '.$monthName,
                'amount' => $enrollment->course->price_aoa,
                'currency' => 'AOA',
                'due_date' => Carbon::create($year, $month, 5),
                'status' => 'pending',
            ]);

            $count++;
        }

        $this->info('Gerados '.$count.' pagamentos para '.$monthName.'.');

        return self::SUCCESS;
    }
}

