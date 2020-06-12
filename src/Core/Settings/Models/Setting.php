<?php

namespace GetCandy\Api\Core\Settings\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Setting extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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
