<?php

namespace GetCandy\Api\Core\Utils\Import;

interface ImportManagerContract
{
    /**
     * Get an OAuth provider implementation.
     *
     * @param  string|null  $driver
     * @return mixed
     */
    public function driver($driver = null);
}
