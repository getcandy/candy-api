<?php

namespace GetCandy\Api\Core\Plugins;

class PluginManager implements PluginManagerInterface
{
    protected $plugins;

    public function __construct()
    {
        $this->plugins = collect();
    }

    public function add($key, $value)
    {
        $this->plugins->put($key, $value);
    }

    public function all()
    {
        return $this->plugins;
    }

    public function get($key)
    {
        return $this->plugins->get($key);
    }
}
