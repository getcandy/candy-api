<?php

namespace GetCandy\Api\Core;

use File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Route;

class GetCandy
{
    protected $isHubRequest = false;

    protected $groups = [];

    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Gets the GetCandy version via composer.
     *
     * @return string
     */
    public static function version()
    {
        try {
            $packages = collect(json_decode(File::get(base_path('vendor/composer/installed.json'))));
        } catch (FileNotFoundException $e) {
            return 'Unknown';
        }

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

    /**
     * Get the default middleware.
     *
     * @return array
     */
    public static function getDefaultMiddleware()
    {
        return [
            'api.currency',
            'api.customer_groups',
            'api.locale',
            'api.tax',
            'api.channels',
            'api.detect_hub',
        ];
    }

    public static function routes(array $options = [], $callback = null)
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $defaultOptions = [
            'namespace' => 'GetCandy\Api\Http\Controllers',
            'middleware' => self::getDefaultMiddleware(),
        ];

        $options = array_merge_recursive($defaultOptions, $options);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }
}
