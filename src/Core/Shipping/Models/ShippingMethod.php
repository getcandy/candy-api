<?php

namespace GetCandy\Api\Core\Shipping\Models;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Scopes\ChannelScope;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Traits\HasChannels;

class ShippingMethod extends BaseModel
{
    use HasAttributes,
        HasChannels;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new ChannelScope);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_data',
        'type',
    ];

    public function zones()
    {
        return $this->belongsToMany(ShippingZone::class, 'shipping_method_zones');
    }

    public function users()
    {
        return $this->belongsToMany(config('auth.providers.users.model'));
    }

    public function prices()
    {
        return $this->hasMany(ShippingPrice::class);
    }

    /**
     * Get the attributes associated to the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'shipping_method_channel')->withPivot('published_at');
    }
}
