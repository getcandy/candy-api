<?php

namespace GetCandy\Api\Http\Controllers\Plugins;

use GetCandy\Api\Core\Plugins\PluginManagerInterface;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Payments\PluginCollection;

class PluginController extends BaseController
{
    public function index(PluginManagerInterface $plugins)
    {
        $plugins = $plugins->all();

        return new PluginCollection($plugins);
    }
}
