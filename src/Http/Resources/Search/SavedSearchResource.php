<?php

namespace GetCandy\Api\Http\Resources\Search;

use GetCandy\Api\Http\Resources\AbstractResource;

class SavedSearchResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
            'payload' => $this->payload,
        ];
    }

    public function includes()
    {
        return [];
    }
}
