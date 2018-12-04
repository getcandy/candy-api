<?php

namespace GetCandy\Api\Http\Resources\Attributes;

use GetCandy\Api\Http\Resources\AbstractResource;

class AttributeResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id
        ];
    }

    public function includes()
    {
        return [
            'group' => ['data' => new AttributeGroupResource($this->whenLoaded('group'))],
        ];
    }
}
