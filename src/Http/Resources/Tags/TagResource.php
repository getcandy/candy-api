<?php

namespace GetCandy\Api\Http\Resources\Tags;

use GetCandy\Api\Http\Resources\AbstractResource;

class TagResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
        ];
    }
}
