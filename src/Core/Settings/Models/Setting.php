<?php

namespace GetCandy\Api\Core\Settings\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Setting extends BaseModel
{
    protected $fillable = [
        'name',
    ];

    public function setContentAttribute($value)
    {
        $this->attributes['content'] = json_encode($value);
    }

    public function getContentAttribute($value)
    {
        return json_decode($value, true);
    }
}
