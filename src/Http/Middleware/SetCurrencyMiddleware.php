<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;

class SetCurrencyMiddleware
{
    /**
     * @var \GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface
     */
    protected $converter;

    public function __construct(CurrencyConverterInterface $converter)
    {
        $this->converter = $converter;
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
        $this->converter->set(
            $request->header('X-CANDY-CURRENCY') ?: $request->currency
        );

        return $next($request);
    }
}
