<?php

namespace GetCandy\Api\Attributes\Models;

use GetCandy\Api\Scaffold\BaseModel;

class Attributable extends BaseModel
{
    public function records($type = null)
    {
        return $this->morphTo('attributable');
    }
}
