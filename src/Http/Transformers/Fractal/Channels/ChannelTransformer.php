<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Channels;

use GetCandy\Api\Channels\Models\Channel;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use Carbon\Carbon;

class ChannelTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'routes'
    ];

    /**
     * Decorates the attribute object for viewing
     * @param  Attribute $product
     * @return Array
     */
    public function transform(Channel $channel)
    {
        $data = [
            'id' => $channel->encodedId(),
            'name' => $channel->name,
            'handle' => $channel->handle,
            'url' => $channel->url,
            'default' => (bool) $channel->default,
            'published_at' => $channel->published_at ? Carbon::parse($channel->published_at)->toIso8601String() : null
        ];

        return $data;
    }

    public function includeRoutes(Channel $channel)
    {
        return $this->item($channel->routes, new RouteTransformer);
    }
}
