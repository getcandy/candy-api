<?php

namespace GetCandy\Api\Core\Languages\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Language extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     * 
     * @var string
     */
    protected $hashids = 'language';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lang', 'iso', 'name', 'default',
    ];
}
