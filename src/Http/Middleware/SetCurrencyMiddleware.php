<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface;

class SetCurrencyMiddleware
{
    /**
     * The Currency converter instance.
     *
     * @var CurrencyConverterInterface
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
        $this->converter->set($request->currency);

        return $next($request);
    }
}
