<?php

namespace GetCandy\Api\Core\Search\Providers\Elastic\Filters;

abstract class AbstractFilter
{
    /**
     * Whether this is a post filter.
     *
     * @var bool
     */
    protected $post = false;

    /**
     * Get the filter.
     *
     * @return mixed
     */
    abstract public function getQuery();

    /**
     * Process the payload.
     *
     * @param mixed $payload
     * @return self
     */
    abstract public function process($payload, $type = null);
}
