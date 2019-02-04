<?php

namespace GetCandy\Api\Http\Controllers\Plugins;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Core\Plugins\PluginManagerInterface;
use GetCandy\Api\Http\Transformers\Fractal\Plugins\PluginTransformer;

class PluginController extends BaseController
{
    public function index(PluginManagerInterface $plugins)
    {
        $plugins = $plugins->all();

        return $this->respondWithCollection($plugins, new PluginTransformer);
    }
}
