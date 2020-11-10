<?php

namespace GetCandy\Api\Core;

use File;
use GetCandy\Api\Core\Channels\Actions\FetchCurrentChannel;
use GetCandy\Api\Core\Channels\Actions\SetCurrentChannel;
use GetCandy\Api\Exceptions\InvalidServiceException;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class GetCandy
{
    /**
     * @var bool
     */
    protected $isHubRequest = false;

    /**
     * @var array
     */
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
            // Composer version 2 support
            $packageManifest = json_decode(File::get(base_path('vendor/composer/installed.json')));
            $packages = is_array($packageManifest) ? collect($packageManifest) : collect($packageManifest->packages);
        } catch (FileNotFoundException $e) {
            return 'Unknown';
        }

        return $packages->first(function ($p) {
            return $p->name === 'getcandy/candy-api';
        })->version;
    }

    /**
     * Sets whether it's a Hub request or not.
     *
     * @param  bool  $bool
     * @return $this
     */
    public function setIsHubRequest($bool)
    {
        $this->isHubRequest = $bool;

        return $this;
    }

    public static function onChannel($channel, \Closure $closure)
    {
        $current = FetchCurrentChannel::run();
        SetCurrentChannel::run([
            'handle' => $channel,
        ]);
        $closure();
        SetCurrentChannel::run([
            'handle' => $current->handle,
        ]);
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

    public function getUserModel()
    {
        return config('auth.providers.users.model', \App\User::class);
    }

    public static function router(array $options = [], $callback = null)
    {
        $callback = $callback ?: function ($router) use ($options) {
            $template = $options['template'] ?? null;
            if ($template) {
                $method = 'template'.ucfirst($template);
                if (method_exists($router, $method)) {
                    $router->{$method}();
                }
            } else {
                $router->all();
            }
        };

        $defaultOptions = [
            'namespace' => '\GetCandy\Api\Http\Controllers',
            'middleware' => self::getDefaultMiddleware(),
        ];

        $options = array_merge_recursive($defaultOptions, $options);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });
    }

    public function __call($name, $params)
    {
        try {
            $resolvingName = Str::snake($name);

            return app("getcandy.{$resolvingName}");
        } catch (\Exception $e) {
            throw new InvalidServiceException("Service \"{$name}\" doesn't exist");
        }
    }
}
