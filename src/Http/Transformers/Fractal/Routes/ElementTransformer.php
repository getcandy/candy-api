<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Routes;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Layouts\LayoutTransformer;
use Illuminate\Database\Eloquent\Model;

class ElementTransformer extends BaseTransformer
{
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'element',
        'layout',
        'routes',
    ];

    public function transform(Model $model)
    {
        return [
            'id' => $model->encodedId(),
            'attribute_data' => $model->attribute_data,
        ];
    }

    public function includeLayout(Model $model)
    {
        return $this->item($model->layout, new LayoutTransformer);
    }

    public function includeRoutes(Model $model)
    {
        return $this->collection($model->routes, new RouteTransformer);
    }
}
