<?php

namespace GetCandy\Api\Core\Attributes\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Attributable extends BaseModel
{
    public function records($type = null)
    {
        return $this->morphTo('attributable');
    }
}
