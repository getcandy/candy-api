<?php

namespace GetCandy\Api\Core\RecycleBin\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class RecycleBin extends BaseModel
{
    protected $table = 'recycle_bin';

    protected $guarded = [];

    protected $hashids = 'recycle_bin';

    /**
     * Get the owning recyclable model.
     */
    public function recyclable()
    {
        return $this->morphTo()->onlyTrashed();
    }
}
