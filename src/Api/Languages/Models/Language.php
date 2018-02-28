<?php

namespace GetCandy\Api\Languages\Models;

use GetCandy\Api\Scaffold\BaseModel;

class Language extends BaseModel
{
    protected $hashids = 'language';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lang', 'iso', 'name', 'default'
    ];
}
