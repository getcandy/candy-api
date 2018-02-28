<?php

namespace GetCandy\Api\Tags\Models;

use GetCandy\Api\Scaffold\BaseModel;

class Taggable extends BaseModel
{

    public function records($type = null)
    {
        return $this->morphTo('taggables');
    }
}
