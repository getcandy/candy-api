<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Routes;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class ElementTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'element',
        'routes',
    ];

    public function transform(Model $model)
    {
        return [
            'id' => $model->encodedId(),
            'attribute_data' => $model->attribute_data,
        ];
    }

    public function includeRoutes(Model $model)
    {
        return $this->collection($model->routes, new RouteTransformer);
    }
}
