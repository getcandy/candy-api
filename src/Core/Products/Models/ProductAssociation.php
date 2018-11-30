<?php

namespace GetCandy\Api\Core\Products\Models;

use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Associations\Models\AssociationGroup;

class ProductAssociation extends BaseModel
{
    /**
     * The Hashid Channel for encoding the id.
     * @var string
     */
    protected $hashids = 'product';

    protected $fillable = [
        'group_id',
        'association_id',
        'product_id',
    ];

    /**
     * Get the attributes associated to the product.
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
