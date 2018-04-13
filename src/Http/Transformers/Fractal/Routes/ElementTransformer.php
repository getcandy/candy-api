<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Routes;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class ElementTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'element',
    ];

    public function transform(Model $model)
    {
        return [
            'id' => $model->encodedId(),
            'attribute_data' => $model->attribute_data,
        ];
    }
}
