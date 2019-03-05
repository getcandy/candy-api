<?php

namespace GetCandy\Api\Http\Resources\Assets;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Tags\TagCollection;

class AssetResource extends AbstractResource
{
    public function payload()
    {
        $data = [
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

        if (! $this->external) {
            $data = array_merge($data, [
                'sub_kind' => $this->sub_kind,
                'extension' => $this->extension,
                'original_filename' => $this->original_filename,
                'size' => $this->size,
                'width' => $this->width,
                'height' => $this->height,
            ]);
        }

        return $data;
    }

    public function includes()
    {
        return [
            'transforms' => new AssetTransformCollection($this->whenLoaded('transforms')),
            'tags' => new TagCollection($this->whenLoaded('tags')),
        ];
    }
}
