<?php

namespace GetCandy\Api\Taxes\Models;

use GetCandy\Api\Scaffold\BaseModel;

class Tax extends BaseModel
{
    protected $hashids = 'tax';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'default',
        'percentage'
    ];
}
