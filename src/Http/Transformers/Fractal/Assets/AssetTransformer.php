<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Assets;

use Storage;
use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Tags\TagTransformer;

class AssetTransformer extends BaseTransformer
{
    protected $defaultIncludes = [
        'tags',
    ];
    protected $availableIncludes = [
        'transforms',
    ];

    /**
     * Decorates the attribute object for viewing.
     * @param  Attribute $product
     * @return array
     */
    public function transform(Asset $asset)
    {
        $data = [
            'id' => $asset->encodedId(),
            'title' => $asset->title,
            'caption' => $asset->caption,
            'kind' => $asset->kind,
            'external' => (bool) $asset->external,
            'thumbnail' => $this->getThumbnail($asset),
            'position' => (int) $asset->position,
            'primary' => (bool) $asset->primary,
        ];

        if (! $asset->external) {
            $data = array_merge($data, [
                'sub_kind' => $asset->sub_kind,
                'extension' => $asset->extension,
                'original_filename' => $asset->original_filename,
                'size' => $asset->size,
                'width' => $asset->width,
                'height' => $asset->height,
                'url' => $this->getUrl($asset),
            ]);
        } else {
            $data['url'] = $asset->location;
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

    protected function getUrl($asset)
    {
        $path = $asset->location.'/'.$asset->filename;

        return Storage::disk($asset->source->disk)->url($path);
    }

    public function includeTransforms($asset)
    {
        return $this->collection($asset->transforms, new AssetTransformTransformer);
    }

    public function includeTags($asset)
    {
        return $this->collection($asset->tags, new TagTransformer);
    }
}
