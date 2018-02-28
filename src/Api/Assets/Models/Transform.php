<?php

namespace GetCandy\Api\Assets\Models;

use GetCandy\Api\Scaffold\BaseModel;

class Transform extends BaseModel
{
    protected $hashids = 'assets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'handle',
        'width',
        'height',
        'constraint',
        'quality'
    ];
}
