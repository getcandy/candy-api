<?php
namespace GetCandy\Api\Shipping\Models;

use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Traits\HasChannels;
use GetCandy\Api\Traits\HasAttributes;
use GetCandy\Api\Channels\Models\Channel;
use GetCandy\Api\Auth\Models\User;

class ShippingMethod extends BaseModel
{
    use HasAttributes,
        HasChannels;

    /**
     * @var string
     */
    protected $hashids = 'main';

    protected $fillable = [
        'attribute_data',
        'type'
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
     * Get the attributes associated to the product
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'shipping_method_channel')->withPivot('published_at');
    }
}
