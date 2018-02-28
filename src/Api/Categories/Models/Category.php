<?php

namespace GetCandy\Api\Categories\Models;

use GetCandy\Api\Channels\Models\Channel;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Customers\Models\CustomerGroup;
use GetCandy\Api\Routes\Models\Route;
use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Traits\Assetable;
use GetCandy\Api\Traits\HasAttributes;
use GetCandy\Api\Traits\HasChannels;
use GetCandy\Api\Traits\HasCustomerGroups;
use GetCandy\Api\Traits\HasRoutes;
use Kalnoy\Nestedset\NodeTrait;

class Category extends BaseModel
{
    use NodeTrait,
        HasAttributes,
        Assetable,
        HasChannels,
        HasRoutes,
        HasCustomerGroups;

    protected $hashids = 'main';

    protected $settings = 'products';

    protected $fillable = [
        'attribute_data', 'parent_id'
    ];

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'id' => $this->encodedId(),
            'thumbnail' => $this->primaryAsset(),
            'parent_id' => $this->encode($this->parent_id),
            'routes' => [
                'data' => $this->routes
            ]
        ]);
    }

    public function getParentIdAttribute($val)
    {
        return $val;
    }
    public function hasChildren()
    {
        return (bool) $this->children()->count();
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo($this, 'parent_id');
    }

    public function getProductCount()
    {
        return $this->belongsToMany(Product::class, 'product_categories')->count();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'category_channel');
    }

    public function customerGroups()
    {
        return $this->belongsToMany(CustomerGroup::class, 'category_customer_group');
    }
}
