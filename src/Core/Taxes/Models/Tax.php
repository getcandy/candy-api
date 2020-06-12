<?php

namespace GetCandy\Api\Core\Taxes\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Tax extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'tax';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'default',
        'percentage',
    ];
}
