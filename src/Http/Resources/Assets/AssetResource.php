<?php

namespace GetCandy\Api\Http\Resources\Assets;

use GetCandy\Api\Http\Resources\AbstractResource;
use GetCandy\Api\Http\Resources\Tags\TagCollection;
use Storage;

class AssetResource extends AbstractResource
{
    public function payload()
    {
        $pivot = $this->resource->pivot;
        $assetable = $this->resource->assetables;

        if ($assetable) {
            $pivot = $assetable;
        }
        $data = [
            'id' => $this->encodedId(),
            'title' => $this->title,
            'type' => $pivot ? $pivot->assetable_type : null,
            'caption' => $this->caption,
            'kind' => $this->kind,
            'external' => (bool) $this->external,
            'thumbnail' => $this->getThumbnail($this->resource),
            'position' => (int) ($pivot ? $pivot->position : 1),
            'primary' => (bool) ($pivot ? $pivot->primary : false),
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

    protected function getThumbnail($asset)
    {
//      return $asset->transforms
        $transform = $asset->transforms->filter(function ($transform) {
            return $transform->transform->handle == 'thumbnail';
        })->first();

        if (! $transform) {
            return;
        }

        $path = $transform->location.'/'.$transform->filename;

        return Storage::disk($asset->source->disk)->url($path);
    }

    public function includes()
    {
        return [
            'transforms' => new AssetTransformCollection($this->whenLoaded('transforms')),
            'tags' => new TagCollection($this->whenLoaded('tags')),
        ];
    }
}
