<?php

namespace GetCandy\Api\Assets\Models;

use GetCandy\Api\Scaffold\BaseModel;

class AssetTransform extends BaseModel
{
    protected $hashids = 'main';

    protected $table = 'asset_transforms';

    public function transform()
    {
        return $this->belongsTo(Transform::class);
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}