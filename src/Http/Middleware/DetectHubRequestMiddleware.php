<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy\Api\Core\CandyApi;

class DetectHubRequestMiddleware
{
    protected $api;

    public function __construct(CandyApi $api)
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
            $this->api->setIsHubRequest(true);
        }

        return $next($request);
    }
}
