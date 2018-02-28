<?php

namespace GetCandy\Api\Traits;

use GetCandy\Api\Assets\Models\Asset;
use GetCandy\Api\Assets\Models\Placeholder;

trait Assetable
{
    public function assets()
    {
        return $this->morphMany(Asset::class, 'assetable');
    }

    public function primaryAsset()
    {
        return $this->assets()->where('primary', '=', 1);
    }
}
