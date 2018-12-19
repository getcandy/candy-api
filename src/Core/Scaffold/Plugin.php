<?php

namespace GetCandy\Api\Core\Scaffold;

use Illuminate\Support\ServiceProvider;

abstract class Plugin extends ServiceProvider
{
    protected $migrations = [];

    protected $routeFiles = [];

    protected $viewDirs = [];

    protected $listen = [];

    protected $subscribe = [];

    protected $commands = [];

    protected $plugins;

    public function boot()
    {
        foreach ($this->migrations as $path) {
            $this->loadMigrationsFrom($path);
        }

        foreach ($this->routeFiles as $file) {
            $this->loadRoutesFrom($file);
        }

        foreach ($this->viewDirs as $namespace => $dir) {
            $this->loadViewsFrom($dir, $namespace);
        }

        $events = app('events');

        $this->commands($this->commands);

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $listener);
            }
        }

        foreach ($this->subscribe as $subscriber) {
            $events->subscribe($subscriber);
        }
    }
}
