<?php

use App\Http\Middleware\CheckAccountStatus;
use App\Http\Middleware\SetCurrencyFromHeader;
use App\Http\Middleware\SetLocaleFromHeader;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web:      __DIR__.'/../routes/web.php',
        api:      __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health:   '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // ── Middlewares globais da API ──────────────────────────────────────
        $middleware->api(prepend: [
            SetLocaleFromHeader::class,
            SetCurrencyFromHeader::class,
        ]);

        // ── Aliases para usar nas rotas por nome ────────────────────────────
        $middleware->alias([
            'check.account.status' => CheckAccountStatus::class,
            'role'                 => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'           => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission'   => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('payments:generate-monthly')
            ->monthlyOn(1, '08:00')
            ->timezone('Africa/Luanda')
            ->withoutOverlapping()
            ->runInBackground();

        $schedule->command('payments:send-reminders')
            ->dailyAt('09:00')
            ->timezone('Africa/Luanda');

        $schedule->command('payments:mark-overdue')
            ->dailyAt('00:01')
            ->timezone('Africa/Luanda');
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Respostas JSON padronizadas para erros da API
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Os dados fornecidos são inválidos.',
                    'errors'  => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Não autenticado. Faça login para continuar.',
                ], 401);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Acesso negado. Não tem permissão para esta operação.',
                ], 403);
            }
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Recurso não encontrado.',
                ], 404);
            }
        });

        $exceptions->render(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Não tem permissão para esta operação.',
                ], 403);
            }
        });

    })->create();
