<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Assets\Models\Asset;

trait Assetable
{
    public function assets()
    {
        return $this->morphMany(Asset::class, 'assetable')->orderBy('position');
    }

    public function primaryAsset()
    {
        return $this->morphOne(Asset::class, 'assetable')->where('primary', '=', 1)->with('transforms');
    }
}
