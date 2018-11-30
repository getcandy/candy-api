<?php

namespace GetCandy\Api\Core\Tags\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Taggable extends BaseModel
{
    public function records($type = null)
    {
        return $this->morphTo('taggables');
    }
}
