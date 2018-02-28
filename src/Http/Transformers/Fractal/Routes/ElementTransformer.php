<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Routes;

use GetCandy\Api\Routes\Models\Route;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use Illuminate\Database\Eloquent\Model;

class ElementTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'element'
    ];

    public function transform(Model $model)
    {
        return [
            'id' => $model->encodedId(),
            'attribute_data' => $model->attribute_data,
        ];
    }
}
