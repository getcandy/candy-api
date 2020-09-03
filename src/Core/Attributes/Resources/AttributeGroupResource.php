<?php

namespace GetCandy\Api\Core\Attributes\Resources;

use GetCandy\Api\Http\Resources\AbstractResource;

class AttributeGroupResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'handle' => $this->handle,
            'position' => (string) $this->position,
        ];
    }

    public function includes()
    {
        return [
            'attributes' => new AttributeCollection($this->whenLoaded('attributes')),
        ];
    }
}
