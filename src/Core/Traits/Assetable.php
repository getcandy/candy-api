<?php

namespace GetCandy\Api\Core\Traits;

use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Assets\Models\Assetable as AssetableModel;

trait Assetable
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function assets()
    {
        return $this->belongsToMany(Asset::class, 'assetables', 'assetable_id')
            ->using(AssetableModel::class)
            ->whereAssetableType(self::class)
            ->withPivot([
                'position',
                'primary',
                'assetable_type',
            ])->orderBy('position', 'asc');
    }

    public function primaryAsset()
    {
        return $this->hasOneDeep(Asset::class, ['assetables'], ['assetable_id'])
        ->whereAssetableType(self::class)
        ->withPivot('assetables', ['primary', 'assetable_type'])
        ->wherePrimary(true);
    }
}
