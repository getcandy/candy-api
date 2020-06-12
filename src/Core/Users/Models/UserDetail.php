<?php

namespace GetCandy\Api\Core\Users\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class UserDetail extends BaseModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

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
