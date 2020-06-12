<?php

namespace GetCandy\Api\Core\Search;

interface ClientContract
{
    /**
     * Searches using the given keywords.
     */
    public function search();
}
