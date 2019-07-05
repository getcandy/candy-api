<?php

namespace GetCandy\Api\Http\Resources\Routes;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Categories\CategoryResource;

class RouteResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'default' => (bool) $this->default,
            'redirect' => (bool) $this->redirect,
            'locale' => $this->locale,
            'path' => $this->path,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => str_slug(class_basename($this->element_type)),
        ];
    }

    public function includes()
    {
        return [
            'element' => ['data' => $this->whenLoaded('element', function () {
                // Need to guess the element
                $class = class_basename(get_class($this->element));
                $resource = 'GetCandy\Api\Http\Resources\\' . str_plural($class) . '\\' . $class . 'Resource';
                if (class_exists($resource)) {
                    return new $resource($this->element);
                }
                return null;
            })]
        ];
    }
}
