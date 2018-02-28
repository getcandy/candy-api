<?php

namespace GetCandy\Api\Currencies\Models;

use GetCandy\Api\Scaffold\BaseModel;

class Currency extends BaseModel
{
    protected $hashids = 'currency';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code', 'name', 'enabled', 'format', 'exchange_rate', 'decimal_point', 'thousand_point', 'default',
    ];
}
