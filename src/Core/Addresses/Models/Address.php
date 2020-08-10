<?php

namespace GetCandy\Api\Core\Addresses\Models;

use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Countries\Models\Country;

class Address extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'user';

    protected $dates = [
        'last_used_at'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function getFieldsAttribute()
    {
        return $this->only([
            'salutation',
            'firstname',
            'lastname',
            'address',
            'address_two',
            'address_three',
            'city',
            'region',
            'country_id',
            'postal_code',
        ]);
    }

    public function setMetaAttribute($value)
    {
        $this->attributes['meta'] = json_encode($value);
    }

    public function getMetaAttribute($value)
    {
        return json_decode($value, true);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->billing ? 'billing' : 'shipping';
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
