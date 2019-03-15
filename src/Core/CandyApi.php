<?php

namespace GetCandy\Api\Core;

class CandyApi
{
    /**
     * Gets the CandyApi version via composer
     *
     * @return string
     */
    public static function version()
    {
        $packages = collect(json_decode(file_get_contents(base_path('vendor/composer/installed.json'))));
        return $packages->first(function ($p) {
            return $p->name === "getcandy/candy-api";
        })->version;
    }
}
