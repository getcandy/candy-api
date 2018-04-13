<?php

namespace GetCandy\Api\Traits;

use GetCandy\Api\Addresses\Models\Address;
use GetCandy\Api\Baskets\Models\Basket;
use GetCandy\Api\Customers\Models\CustomerGroup;
use GetCandy\Api\Languages\Models\Language;
use GetCandy\Api\Orders\Models\Order;
use GetCandy\Api\Users\Models\UserDetail;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

trait HasCandy
{
    use Hashids,
        HasApiTokens,
        HasRoles;

    protected $hashids = 'user';

    public function groups()
    {
        return $this->belongsToMany(CustomerGroup::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function getFieldsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function baskets()
    {
        return $this->hasMany(Basket::class);
    }

    public function latestBasket()
    {
        return $this->hasOne(Basket::class)->orderBy('created_at', 'DESC');
    }

    public function setFieldsAttribute($value)
    {
        $this->attributes['fields'] = json_encode($value);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->withoutGlobalScope('open')->withoutGlobalScope('not_expired');
    }

    public function details()
    {
        return $this->hasOne(UserDetail::class);
    }
}
