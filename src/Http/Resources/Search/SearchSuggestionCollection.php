<?php

namespace GetCandy\Api\Http\Resources\Search;

use GetCandy\Api\Http\Resources\AbstractCollection;

class SearchSuggestionCollection extends AbstractCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = SearchSuggestionResource::class;
}
