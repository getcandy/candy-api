<?php

namespace GetCandy\Api\Http\Resources\Channels;

use Carbon\Carbon;
use GetCandy\Api\Http\Resources\AbstractResource;

class ChannelResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'handle' => $this->handle,
            'url' => $this->url,
            'default' => (bool) $this->default,
            'published_at' => $this->when($this->resource->pivot, function () {
                return $this->resource->pivot->published_at ? Carbon::parse($this->resource->pivot->published_at)->toIso8601String() : null;
            }),
        ];
    }
}
