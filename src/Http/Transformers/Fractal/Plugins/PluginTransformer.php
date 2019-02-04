<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Plugins;

use GetCandy\Api\Core\Plugins\Plugin;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class PluginTransformer extends BaseTransformer
{
    protected $availableIncludes = [];

    public function transform(Plugin $plugin)
    {
        dd($plugin);

        return [
            'foo' => 'bar',
        ];
    }
}
