<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocaleFromHeader
{
    protected array $supported = ['pt', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', 'pt');

        // Normaliza — aceita "pt-AO", "pt-BR", "en-US", etc.
        $lang = strtolower(substr($locale, 0, 2));

        if (in_array($lang, $this->supported)) {
            app()->setLocale($lang);
        } else {
            app()->setLocale('pt');
        }

        return $next($request);
    }
}
