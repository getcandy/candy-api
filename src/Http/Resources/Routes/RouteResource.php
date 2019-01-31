<?php

namespace GetCandy\Api\Http\Resources\Routes;

use GetCandy\Api\Http\Resources\AbstractResource;

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
        return [];
    }
}
