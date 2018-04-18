<?php

namespace GetCandy\Api\Search;

interface ClientContract
{
    /**
     * Searches using the given keywords.
     * @param  string $keywords
     */
    public function search($keywords);
}
