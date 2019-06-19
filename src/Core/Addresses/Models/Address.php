<?php

namespace GetCandy\Api\Core\Addresses\Models;

use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Scaffold\BaseModel;

class Address extends BaseModel
{
    protected $hashids = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address',
        'address_three',
        'address_two',
        'billing',
        'city',
        'country',
        'county',
        'default',
        'firstname',
        'lastname',
        'shipping',
        'state',
        'zip',
    ];

    public function getFieldsAttribute()
    {
        return $this->only([
            'firstname',
            'lastname',
            'address',
            'address_two',
            'address_three',
            'city',
            'county',
            'state',
            'country',
            'zip',
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->billing ? 'billing' : 'shipping';
    }
}
