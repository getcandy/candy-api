<?php

namespace GetCandy\Api\Providers;

use File;
use Illuminate\Support\ServiceProvider;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $loader = require base_path().'/vendor/autoload.php';

        $pluginsDir = base_path('plugins');

        if (File::exists($pluginsDir)) {
            $list = File::directories($pluginsDir);
            foreach ($list as $dir) {
                $config = require $dir.'/candy.php';

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
        //
    }
}
