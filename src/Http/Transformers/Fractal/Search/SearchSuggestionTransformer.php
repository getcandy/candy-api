<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Search;

use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Routes\RouteTransformer;

class SearchSuggestionTransformer extends BaseTransformer
{
    protected $defaultIncludes = ['routes', 'thumbnail'];

    public function transform(Model $model)
    {
        return [
            'id' => $model->encodedId(),
            'name' => $model->name,
        ];
    }

    protected function includeRoutes($model)
    {
        return $this->collection($model->routes, new RouteTransformer);
    }
}
