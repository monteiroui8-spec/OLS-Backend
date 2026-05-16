<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token): string {
            $base = config('app.frontend_url', config('app.url', 'http://localhost'));
            $base = rtrim((string) $base, '/');
            $email = method_exists($notifiable, 'getEmailForPasswordReset')
                ? $notifiable->getEmailForPasswordReset()
                : ($notifiable->email ?? '');

            return $base . '/reset-password?token=' . urlencode($token) . '&email=' . urlencode((string) $email);
        });

        // /auth/login, /auth/register, /auth/forgot-password
        // Limite apertado só para os endpoints de autenticação sensíveis
        RateLimiter::for('auth', function (Request $request) {
            // /auth/me e /auth/logout têm utilizador autenticado — limite muito mais alto
            if ($request->user()) {
                return Limit::perMinute(120)->by($request->user()->id);
            }

            // Login/registo sem sessão: 10 tentativas por 15 minutos por IP
            return Limit::perMinutes(15, 10)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            if ($request->user()) {
                return Limit::perMinute(300)->by($request->user()->id);
            }

            return Limit::perMinute(100)->by($request->ip());
        });

        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
