<?php

namespace GetCandy\Api\Core\Utils\Import;

interface ImportManagerContract
{
    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     * @return \Laravel\Socialite\Contracts\Provider
     */
    public function driver($driver = null);
}
