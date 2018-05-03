<?php

namespace GetCandy\Api\Core\Assets\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

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
        'quality',
    ];
}
