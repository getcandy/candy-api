<?php

namespace GetCandy\Api\Core\Countries\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;

class Country extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'iso_a_2',
        'iso_a_3',
        'iso_numeric',
        'region',
        'sub_region',
    ];

    public function scopeEnabled($query)
    {
        return $query->whereEnabled(true);
    }

    public function scopePreferred($query)
    {
        return $query->wherePreferred(true);
    }

    public function scopeDisabled($query)
    {
        return $query->whereEnabled(false);
    }
}
