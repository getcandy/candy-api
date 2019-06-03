<?php

namespace GetCandy\Api\Core\Auth\Models;

use Laravel\Passport\HasApiTokens;
use GetCandy\Api\Core\Traits\Hashids;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Users\Models\UserDetail;
use GetCandy\Api\Core\Addresses\Models\Address;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use Illuminate\Foundation\Auth\User as Authenticatable;
use GetCandy\Plugins\LegacyPassword\Models\LegacyPassword;

class User extends Authenticatable
{
    use Notifiable,
        Hashids,
        HasApiTokens,
        HasRoles;

    protected $hashids = 'user';

    protected $guard_name = 'api';

    public function getAuthPassword()
    {
        if (! $this->password) {
            $password = $this->legacypassword;

            return [
                'password' => $password->password,
                'salt' => $password->salt,
            ];
        }

        return $this->password;
    }

    public function legacypassword()
    {
        return $this->hasOne(LegacyPassword::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'firstname', 'lastname', 'email',  'password', 'role', 'company_name',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @param Builder $qb
     * @return Builder
     */
    public function scopeDefault($qb)
    {
        return $qb->where('default', 1);
    }

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
