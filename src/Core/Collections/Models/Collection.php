<?php

namespace GetCandy\Api\Core\Collections\Models;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\Assetable;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Traits\HasChannels;
use GetCandy\Api\Core\Traits\HasCustomerGroups;
use GetCandy\Api\Core\Traits\HasRoutes;
use NeonDigital\Drafting\Draftable;
use NeonDigital\Versioning\Versionable;

class Collection extends BaseModel
{
    use Assetable,
        HasAttributes,
        HasChannels,
        HasRoutes,
        HasCustomerGroups,
        Draftable,
        Versionable;

    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'main';

    /**
     * @var string
     */
    protected $settings = 'collections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'attribute_data',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}
