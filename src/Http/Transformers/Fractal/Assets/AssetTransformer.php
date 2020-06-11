<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Assets;

use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Tags\TagTransformer;
use Storage;

class AssetTransformer extends BaseTransformer
{
    /**
     * Include resources without needing it to be requested.
     *
     * @var array
     */
    protected $defaultIncludes = [
        'tags',
    ];
    
    /**
     * Resources that can be included if requested.
     *
     * @var array
     */
    protected $availableIncludes = [
        'transforms',
    ];

    /**
     * Decorates the asset object for viewing.
     * 
     * @param  \GetCandy\Api\Core\Assets\Models\Asset  $asset
     * @return array
     */
    public function transform($asset)
    {
        $data = [
            'id' => $asset->encodedId(),
            'title' => $asset->title,
            'caption' => $asset->caption,
            'kind' => $asset->kind,
            'external' => (bool) $asset->external,
            'thumbnail' => $this->getThumbnail($asset),
            'position' => $asset->pivot ? $asset->pivot->position : 1,
            'primary' => (bool) $asset->pivot ? $asset->pivot->primary : false,
        ];

        if (! $asset->external) {
            $data = array_merge($data, [
                'sub_kind' => $asset->sub_kind,
                'extension' => $asset->extension,
                'original_filename' =>$asset->original_filename,
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
