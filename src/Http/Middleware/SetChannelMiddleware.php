<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy\Api\Core\Channels\Actions\SetCurrentChannel;

class SetChannelMiddleware
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
        SetCurrentChannel::run([
            'handle' => $request->header('X-CANDY-CHANNEL') ?: $request->channel,
        ]);

        return $next($request);
    }
}
