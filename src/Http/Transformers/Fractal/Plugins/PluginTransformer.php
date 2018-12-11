<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Plugins;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Core\Plugins\Plugin;

class PluginTransformer extends BaseTransformer
{
    protected $availableIncludes = [];

    public function transform(Plugin $plugin)
    {
        dd($plugin);
        return [
            'foo' => 'bar'
        ];
    }
}