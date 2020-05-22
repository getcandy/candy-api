<?php

namespace GetCandy\Api\Core\Assets\Versioning;

use Illuminate\Database\Eloquent\Model;
use NeonDigital\Versioning\Interfaces\VersionerInterface;
use NeonDigital\Versioning\Versioners\AbstractVersioner;

class AssetVersioner extends AbstractVersioner implements VersionerInterface
{
    public function create(Model $asset, $relationId = null)
    {
        $this->createFromObject($asset, $relationId, $asset->pivot);
    }
}
