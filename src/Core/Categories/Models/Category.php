<?php

namespace GetCandy\Api\Core\Categories\Models;

use Kalnoy\Nestedset\NodeTrait;
use GetCandy\Api\Core\Traits\Assetable;
use GetCandy\Api\Core\Traits\HasRoutes;
use GetCandy\Api\Core\Traits\HasLayouts;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasChannels;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Traits\HasCustomerGroups;

class Category extends BaseModel
{
    use NodeTrait,
        HasAttributes,
        HasLayouts,
        Assetable,
        HasChannels,
        HasRoutes,
        HasCustomerGroups;

    protected $hashids = 'main';

    protected $settings = 'categories';

    protected $fillable = [
        'attribute_data', 'parent_id',
    ];

    public function toArray()
    {
        return array_merge(parent::toArray(), [
            'id' => $this->encodedId(),
            'parent_id' => $this->encode($this->parent_id),
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
        return $this->hasMany(self::class, 'parent_id');
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
        return $this->belongsToMany(Product::class, 'product_categories')
            ->withPivot('position')
            ->orderBy('product_categories.position', 'asc');
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'category_channel');
    }
}
