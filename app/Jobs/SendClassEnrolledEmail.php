<?php

namespace App\Jobs;

use App\Models\ClassGroup;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendClassEnrolledEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public readonly User $user,
        public readonly ClassGroup $class,
    ) {}

    public function handle(): void
    {
        $lang = $this->user->preferred_language ?? 'pt';

        $subject = $lang === 'en'
            ? "You've been enrolled in {$this->class->name}"
            : "Foi inscrito na turma {$this->class->name}";

        Mail::send(
            'emails.class_enrolled',
            [
                'user'  => $this->user,
                'class' => $this->class->load(['course', 'teacher.user', 'schedules']),
                'lang'  => $lang,
            ],
            function ($message) use ($subject) {
                $message
                    ->to($this->user->email, $this->user->full_name)
                    ->subject($subject);
            }
        );
    }
}
