<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use TaxCalculator;

class SetTaxMiddleware
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
        // if (! $request->excl_tax) {
        TaxCalculator::setTax(
                app('api')->taxes()->getDefaultRecord()
            );
        // }

        return $next($request);
    }
}
