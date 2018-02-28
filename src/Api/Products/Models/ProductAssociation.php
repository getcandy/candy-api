<?php

namespace GetCandy\Api\Products\Models;

use GetCandy\Api\Associations\Models\AssociationGroup;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Scaffold\BaseModel;

class ProductAssociation extends BaseModel
{

    /**
     * The Hashid Channel for encoding the id
     * @var string
     */
    protected $hashids = 'product';

    /**
     * Get the attributes associated to the product
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function parent()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function association()
    {
        return $this->belongsTo(Product::class, 'association_id');
    }

    public function group()
    {
        return $this->belongsTo(AssociationGroup::class);
    }
}
