<?php

namespace GetCandy\Api\Providers;

use File;
use GetCandy\Api\Core\Plugins\Plugin;
use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Plugins\PluginManager;
use GetCandy\Api\Core\Plugins\PluginManagerInterface;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(PluginManagerInterface $plugins)
    {
        $loader = require base_path().'/vendor/autoload.php';

        $pluginsDir = base_path('plugins');

        if (File::exists($pluginsDir)) {
            $list = File::directories($pluginsDir);
            foreach ($list as $dir) {
                $config = require $dir.'/candy.php';

                $handle = strtolower($config['namespace_suffix']);

                $plugin = new Plugin($handle, $dir);
                $plugin->setConfig($config);

                $plugins->add($handle, $plugin);

                $namespace = 'GetCandy\\Plugins\\'.$config['namespace_suffix'].'\\';

                $loader->setPsr4($namespace, $dir.'/src/');

                $serviceProvider = $namespace.$config['service_provider'];

                $this->app->register($serviceProvider);
            }
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PluginManagerInterface::class, function ($app) {
            return new PluginManager;
        });
    }
}
