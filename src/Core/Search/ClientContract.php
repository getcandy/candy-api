<?php

namespace GetCandy\Api\Core\Search;

interface ClientContract
{
    /**
     * Searches using the given keywords.
     * @param  string $keywords
     */
    public function search();
}
