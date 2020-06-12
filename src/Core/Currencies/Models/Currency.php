<?php

namespace GetCandy\Api\Core\Currencies\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Currency extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
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
