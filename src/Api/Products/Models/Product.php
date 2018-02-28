<?php

namespace GetCandy\Api\Products\Models;

use GetCandy\Api\Traits\Indexable;
use GetCandy\Api\Traits\Assetable;
use GetCandy\Api\Traits\HasRoutes;
use GetCandy\Api\Pages\Models\Page;
use GetCandy\Api\Traits\HasChannels;
use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Traits\HasAttributes;
use GetCandy\Api\Layouts\Models\Layout;
use GetCandy\Api\Traits\HasTranslations;
use GetCandy\Api\Traits\HasCustomerGroups;
use GetCandy\Api\Discounts\Models\Discount;
use GetCandy\Api\Categories\Models\Category;
use Illuminate\Database\Eloquent\SoftDeletes;
use GetCandy\Api\Attributes\Models\Attribute;
use GetCandy\Api\Collections\Models\Collection;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;

class Product extends BaseModel
{
    use Assetable,
        HasCustomerGroups,
        HasAttributes,
        HasChannels,
        HasRoutes,
        SoftDeletes,
        Indexable;

    protected $settings = 'products';

    protected $dates = ['deleted_at'];

    public $transformer = ProductTransformer::class;

    /**
     * The Hashid Channel for encoding the id
     * @var string
     */
    protected $hashids = 'product';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'price', 'attribute_data', 'option_data'
    ];

    /**
     * Sets the option data attribute
     * [
     *     [
     *         'label' => [
     *             'en' => 'Colour'
     *         ],
     *         'options' => [
     *             [
     *                 position: 1,
     *                 values: [
     *                     'en' => 'Espresso',
     *                     'fr' => 'Espresso'
     *                 ]
     *             ]
     *         ]
     *     ]
     * ]
     * @param array $value [description]
     */
    public function setOptionDataAttribute($value)
    {
        $options = [];
        $parentPosition = 1;

        foreach ($value as $option) {
            $label = reset($option['label']);
            $options[str_slug($label)] = $option;
            $childOptions = [];
            $position = 1;

            foreach ($option['options'] as $child) {
                $childLabel = reset($child['values']);
                $childOptions[str_slug($childLabel)] = $child;
                $childOptions[str_slug($childLabel)]['position'] = $position;
                $position++;
            }
            $options[str_slug($label)]['position'] = $parentPosition;
            $options[str_slug($label)]['options'] = $childOptions;
            $parentPosition++;
        }
        $this->attributes['option_data'] = json_encode($options);
    }

    public function getOptionDataAttribute($value)
    {
        return json_decode($value, true);
    }

    /**
     * Get the attributes associated to the product
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function collections()
    {
        return $this->belongsToMany(Collection::class)->withTimestamps();
    }

    /**
     * Get the related family
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function family()
    {
        return $this->belongsTo(ProductFamily::class, 'product_family_id');
    }

    /**
     * Get the products page
     * @return Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function page()
    {
        return $this->morphOne(Page::class, 'element');
    }

    public function layout()
    {
        return $this->belongsTo(Layout::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function firstVariant()
    {
        return $this->hasOne(ProductVariant::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories');
    }

    public function associations()
    {
        return $this->hasMany(ProductAssociation::class);
    }

    public function discounts()
    {
        return $this->morphMany(Discount::class, 'eligible');
    }
}
