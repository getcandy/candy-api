<?php

namespace GetCandy\Api\Http\Resources\Associations;

use GetCandy\Api\Http\Resources\AbstractResource;

class AssociationGroupResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'handle' => $this->handle,
        ];
    }
}
