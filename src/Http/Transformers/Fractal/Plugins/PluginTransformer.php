<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Plugins;

use GetCandy\Api\Core\Plugins\Plugin;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class PluginTransformer extends BaseTransformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [];

    public function transform(Plugin $plugin)
    {
        dd($plugin);

        return [
            'foo' => 'bar',
        ];
    }
}
