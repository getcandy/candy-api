<?php

namespace GetCandy\Api\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DynamicResource extends JsonResource
{
    public function toArray($request)
    {
        if ($resource = $this->resource->resource) {
            return new $resource($this->resource);
        }

        return $this->resource->toArray();
    }
}
