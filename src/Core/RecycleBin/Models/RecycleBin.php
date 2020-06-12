<?php

namespace GetCandy\Api\Core\RecycleBin\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class RecycleBin extends BaseModel
{
    protected $table = 'recycle_bin';

    protected $guarded = [];

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'recycle_bin';

    /**
     * Get the owning recyclable model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function recyclable()
    {
        return $this->morphTo()->onlyTrashed();
    }
}
