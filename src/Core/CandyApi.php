<?php

namespace GetCandy\Api\Core;

class CandyApi
{
    protected $isHubRequest = false;

    /**
     * Gets the CandyApi version via composer.
     *
     * @return string
     */
    public static function version()
    {
        $packages = collect(json_decode(file_get_contents(base_path('vendor/composer/installed.json'))));

        return $packages->first(function ($p) {
            return $p->name === 'getcandy/candy-api';
        })->version;
    }

    /**
     * Sets whether it's a hub request or not.
     *
     * @param bool $bool
     * @return self
     */
    public function setIsHubRequest($bool)
    {
        $this->isHubRequest = $bool;

        return $this;
    }

    /**
     * Gets the value for isHubRequest.
     *
     * @return bool
     */
    public function isHubRequest()
    {
        return $this->isHubRequest;
    }
}
