<?php

namespace GetCandy\Api\Collections\Models;

use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Traits\Assetable;
use GetCandy\Api\Traits\HasAttributes;
use GetCandy\Api\Traits\HasTranslations;
use GetCandy\Api\Traits\HasChannels;
use GetCandy\Api\Traits\HasRoutes;
use GetCandy\Api\Traits\HasCustomerGroups;

class Collection extends BaseModel
{
    use Assetable,
        HasAttributes,
        HasChannels,
        HasRoutes,
        HasCustomerGroups;

    protected $hashids = 'channel';

    protected $settings = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_data'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
