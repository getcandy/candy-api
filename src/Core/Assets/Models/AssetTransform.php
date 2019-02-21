<?php

namespace GetCandy\Api\Core\Assets\Models;

use Storage;
use GetCandy\Api\Core\Scaffold\BaseModel;

class AssetTransform extends BaseModel
{
    protected $hashids = 'main';

    protected $table = 'asset_transforms';

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'url' => $this->url,
        ]);
    }

    public function getUrlAttribute()
    {
        return Storage::disk($this->asset->source->disk)->url($this->location.'/'.$this->filename);
    }

    public function transform()
    {
        return $this->belongsTo(Transform::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
