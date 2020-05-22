<?php

namespace GetCandy\Api\Core\Assets\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Assetable extends Pivot
{
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
