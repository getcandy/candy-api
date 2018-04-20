<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Channels;

use Carbon\Carbon;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class ChannelTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'routes',
    ];

    /**
     * Decorates the attribute object for viewing.
     * @param  Attribute $product
     * @return array
     */
    public function transform(Channel $channel)
    {
        $data = [
            'id' => $channel->encodedId(),
            'name' => $channel->name,
            'handle' => $channel->handle,
            'url' => $channel->url,
            'default' => (bool) $channel->default,
            'published_at' => $channel->published_at ? Carbon::parse($channel->published_at)->toIso8601String() : null,
        ];

        return $data;
    }

    public function includeRoutes(Channel $channel)
    {
        return $this->item($channel->routes, new RouteTransformer);
    }
}
