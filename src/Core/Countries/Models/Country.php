<?php

namespace GetCandy\Api\Core\Countries\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasTranslations;

class Country extends BaseModel
{
    use HasTranslations;

    /**
     * @var string
     */
    protected $hashids = 'main';

    protected $fillable = [
        'name',
        'iso_a_2',
        'iso_a_3',
        'iso_numeric',
        'region',
        'sub_region',
    ];
}
