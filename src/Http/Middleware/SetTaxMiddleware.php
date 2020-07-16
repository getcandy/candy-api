<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy;
use GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface;

class SetTaxMiddleware
{
    /**
     * @var \GetCandy\Api\Core\Taxes\Interfaces\TaxCalculatorInterface
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
            GetCandy::taxes()->getDefaultRecord()
        );
        // }

        return $next($request);
    }
}
