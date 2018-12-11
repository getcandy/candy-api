<?php

namespace GetCandy\Api\Http\Resources\Collections;

use GetCandy\Api\Http\Resources\AbstractResource;

class CollectionResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
        ];
    }
}