<?php

namespace GetCandy\Api\Http\Resources\Search;

use GetCandy\Api\Http\Resources\AbstractResource;

class SearchSuggestionResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encoded_id,
            'name' => $this->name,
        ];
    }
}
