<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface;

class SetTaxMiddleware
{
    /**
     * The tax calculator instance.
     *
     * @var TaxCalculatorInterface
     */
    protected $tax;

    public function __construct(TaxCalculatorInterface $tax)
    {
        $this->tax = $tax;
    }

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
        $this->tax->setTax(
            app('api')->taxes()->getDefaultRecord()
        );
        // }

        return $next($request);
    }
}
