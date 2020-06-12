<?php

namespace GetCandy\Api\Core\Assets\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Transform extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
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
