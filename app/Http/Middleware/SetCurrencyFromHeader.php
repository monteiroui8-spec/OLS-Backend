<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrencyFromHeader
{
    protected array $supported = ['AOA', 'EUR', 'USD'];

    public function handle(Request $request, Closure $next): Response
    {
        $currency = strtoupper($request->header('X-Currency', 'AOA'));

        if (! in_array($currency, $this->supported)) {
            $currency = 'AOA';
        }

        // Disponível globalmente via app('currency')
        app()->bind('currency', fn () => $currency);

        // Também coloca na request para controllers acederem facilmente
        $request->merge(['_currency' => $currency]);

        return $next($request);
    }
}
