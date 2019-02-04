<?php

namespace GetCandy\Api\Http\Resources\Assets;

use Storage;
use GetCandy\Api\Http\Resources\AbstractResource;

class AssetTransformResource extends AbstractResource
{
    public function payload()
    {
        $this->load(['asset', 'transform']);

        return [
            'id' => $this->encodedId(),
            'handle' => $this->transform->handle,
            'url' => $this->getUrl(),
        ];
    }

    protected function getUrl()
    {
        $path = $this->location.'/'.$this->filename;

        return Storage::disk($this->asset->source->disk)->url($path);
    }

    public function includes()
    {
        return [
        ];
    }
}
