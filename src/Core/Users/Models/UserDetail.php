<?php

namespace GetCandy\Api\Core\Users\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class UserDetail extends BaseModel
{
    protected $guarded = [];

    public function getFieldsAttribute($val)
    {
        return json_decode($val);
    }

    public function setFieldsAttribute($val)
    {
        $this->attributes['fields'] = json_encode($val);
    }
}
