<?php

namespace GetCandy\Api\Http\Transformers\Fractal\Assets;

use Storage;
use GetCandy\Api\Core\Assets\Models\AssetTransform;
use GetCandy\Api\Http\Transformers\Fractal\BaseTransformer;

class AssetTransformTransformer extends BaseTransformer
{
    public function transform(AssetTransform $transform)
    {
        return [
            'id' => $transform->encodedId(),
            'handle' => $transform->transform->handle,
            'url' => $this->getUrl($transform),
        ];
    }

    protected function getUrl($transform)
    {
        $path = $transform->location.'/'.$transform->filename;

        return Storage::disk($transform->asset->source->disk)->url($path);
    }
}
