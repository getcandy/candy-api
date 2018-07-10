<?php

namespace GetCandy\Api\Core\Traits;

use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Users\Models\UserDetail;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Baskets\Models\SavedBasket;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

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

    public function savedBaskets()
    {
        return $this->hasManyThrough(SavedBasket::class, Basket::class);
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
        return $this->hasMany(Order::class)->orderBy('reference', 'desc')->withoutGlobalScope('open')->withoutGlobalScope('not_expired');
    }

    public function details()
    {
        return $this->hasOne(UserDetail::class);
    }
}
