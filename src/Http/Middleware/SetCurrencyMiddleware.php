<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use CurrencyConverter;

class SetCurrencyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        CurrencyConverter::set($request->currency);

        return $next($request);
    }
}
