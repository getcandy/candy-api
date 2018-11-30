<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Layouts;

use GetCandy\Api\Core\Layouts\Models\Layout;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class LayoutTransformer extends BaseTransformer
{
    protected $availableIncludes = [];

    public function transform(Layout $layout)
    {
        return [
            'id' => $layout->encodedId(),
            'name' => $layout->name,
            'handle' => $layout->handle,
        ];
    }
}
