<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Search;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Routes\RouteTransformer;
use Illuminate\Database\Eloquent\Model;

class SearchSuggestionTransformer extends BaseTransformer
{
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
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
