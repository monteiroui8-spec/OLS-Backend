<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAccountDeactivatedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(public readonly User $user) {}

    public function handle(): void
    {
        $lang = $this->user->preferred_language ?? 'pt';

        $subject = $lang === 'en'
            ? 'Your Olsangola account has been deactivated'
            : 'A sua conta Olsangola foi desactivada';

        Mail::send(
            'emails.account_deactivated',
            ['user' => $this->user, 'lang' => $lang],
            function ($message) use ($subject) {
                $message
                    ->to($this->user->email, $this->user->full_name)
                    ->subject($subject);
            }
        );
    }
}
