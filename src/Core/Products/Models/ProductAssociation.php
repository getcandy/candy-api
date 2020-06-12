<?php

namespace GetCandy\Api\Core\Products\Models;

use GetCandy\Api\Core\Associations\Models\AssociationGroup;
use GetCandy\Api\Core\Scaffold\BaseModel;

class ProductAssociation extends BaseModel
{
    /**
     * The Hashid connection name for enconding the id.
     *
     * @var string
     */
    protected $hashids = 'product';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'group_id',
        'association_id',
        'product_id',
    ];

    /**
     * Get the parent product associated.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
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
