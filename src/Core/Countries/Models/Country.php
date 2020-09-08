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
        'enabled',
        'preferred',
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

    public function states()
    {
        return $this->hasMany(State::class);
    }
}
