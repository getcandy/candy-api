<?php

namespace GetCandy\Api\Http\Middleware;

use Closure;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;

class SetChannelMiddleware
{
    /**
     * The channel factory interface.
     *
     * @var ChannelFactoryInterface
     */
    protected $channel;

    public function __construct(ChannelFactoryInterface $channel)
    {
        $this->channel = $channel;
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
        // Only set if one isn't set so it's easier to override it.
        $this->channel->set(
            $request->header('X-CANDY-CHANNEL') ?: $request->channel
        );

        return $next($request);
    }
}
