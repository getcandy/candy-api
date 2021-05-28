<?php

namespace GetCandy\Api\Core\Routes\Resources;

use GetCandy\Api\Core\Languages\Resources\LanguageResource;
use GetCandy\Api\Http\Resources\AbstractResource;

class RouteResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'default' => (bool) $this->default,
            'redirect' => (bool) $this->redirect,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => str_slug(class_basename($this->element_type)),
            'drafted_at' => $this->drafted_at,
        ];
    }

    public function includes()
    {
        return [
            'language' => $this->include('language', LanguageResource::class),
            'draft' => $this->include('draft', self::class),
            'published_parent' => $this->include('publishedParent', self::class),
            'element' => $this->whenLoaded('element', function () {
                // Need to guess the element
                $class = class_basename(get_class($this->element));
                $resource = 'GetCandy\Api\Http\Resources\\'.str_plural($class).'\\'.$class.'Resource';
                if (class_exists($resource)) {
                    return ['data' => new $resource($this->element)];
                }

                // Try and guess relative to the actual class
                $classSegments = explode('\\', get_class($this->element));

                foreach ($classSegments as $index => $segment) {
                    if ($segment == 'Models' || $segment == $class) {
                        unset($classSegments[$index]);
                    }
                }

                array_push($classSegments, 'Http');
                array_push($classSegments, 'Resources');
                array_push($classSegments, $class.'Resource');

                $resourceClass = implode('\\', $classSegments);

                if (class_exists($resourceClass)) {
                    return [
                        'data' => new $resourceClass($this->element),
                    ];
                }
            }),
        ];
    }
}
