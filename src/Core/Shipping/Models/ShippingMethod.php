<?php

namespace GetCandy\Api\Core\Shipping\Models;

use GetCandy\Api\Core\Auth\Models\User;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasChannels;
use GetCandy\Api\Core\Scopes\ChannelScope;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Channels\Models\Channel;

class ShippingMethod extends BaseModel
{
    use HasAttributes,
        HasChannels;

    /**
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
        return $this->belongsToMany(User::class);
    }

    public function prices()
    {
        return $this->hasMany(ShippingPrice::class);
    }

    /**
     * Get the attributes associated to the product.
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'shipping_method_channel')->withPivot('published_at');
    }
}
