<?php

namespace GetCandy\Api\Core;

use Illuminate\Support\Facades\Route;

class GetCandy
{
    protected $isHubRequest = false;

    /**
     * Gets the GetCandy version via composer.
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

    public static function routes(array $options = [], $callback = null)
    {
        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $defaultOptions = [
            'namespace' => 'GetCandy\Api\Http\Controllers',
            'middleware' => [
                'api.currency',
                'api.customer_groups',
                'api.locale',
                'api.tax',
                'api.channels',
                'api.detect_hub'
            ]
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }
}
