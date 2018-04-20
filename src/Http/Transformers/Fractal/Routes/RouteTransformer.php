<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Routes;

use GetCandy\Api\Core\Routes\Models\Route;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class RouteTransformer extends BaseTransformer
{
    protected $availableIncludes = [
        'element',
    ];

    public function transform(Route $route)
    {
        return [
            'id' => $route->encodedId(),
            'default' => (bool) $route->default,
            'redirect' => (bool) $route->redirect,
            'locale' => $route->locale,
            'slug' => $route->slug,
            'description' => $route->description,
            'type' => str_slug(class_basename($route->element_type)),
        ];
    }

    public function includeElement(Route $route)
    {
        if (! $route->element) {
            return $this->null();
        }

        return $this->item($route->element, new ElementTransformer);
    }
}
