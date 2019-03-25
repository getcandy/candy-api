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

    public function getFullNameAttribute()
    {
        return trim($this->title.' '.$this->firstname.' '.$this->lastname);
    }

    public function setFieldsAttribute($val)
    {
        $this->attributes['fields'] = json_encode($val);
    }
}
