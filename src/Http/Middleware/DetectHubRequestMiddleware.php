<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy;

class DetectHubRequestMiddleware
{
    protected $api;

    public function __construct(GetCandy $api)
    {
        $this->api = $api;
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
        if ($request->headers->has('X-CANDY-HUB')) {
            GetCandy::setIsHubRequest(true);
        }

        return $next($request);
    }
}
