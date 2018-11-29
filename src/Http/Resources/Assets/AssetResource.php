<?php

namespace GetCandy\Api\Http\Resources\Assets;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Routes\RouteCollection;
use GetCandy\Api\Http\Resources\Layouts\LayoutResource;

class AssetResource extends AbstractResource
{
    public function payload()
    {
        return [
            'id' => $this->encodedId(),
            'id' => $this->encodedId(),
            'title' => $this->title,
            'caption' => $this->caption,
            'kind' => $this->kind,
            'external' => (bool) $this->external,
            // 'thumbnail' => $this->getThumbnail($asset),
            'position' => (int) $this->position,
            'primary' => (bool) $this->primary,
            'url' => $this->url,
        ];
    }

    public function includes()
    {
        return [
            'transforms' => new AssetTransformCollection($this->whenLoaded('transforms')),
        ];
    }
}