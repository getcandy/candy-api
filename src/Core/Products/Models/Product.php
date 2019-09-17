<?php

namespace GetCandy\Api\Core\Products\Models;

use GetCandy\Api\Core\Traits\Assetable;
use GetCandy\Api\Core\Traits\Indexable;
use GetCandy\Api\Core\Traits\HasRoutes;
use GetCandy\Api\Core\Pages\Models\Page;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Traits\HasChannels;
use GetCandy\Api\Core\Scopes\ChannelScope;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Layouts\Models\Layout;
use Illuminate\Database\Eloquent\SoftDeletes;
use GetCandy\Api\Core\Traits\HasCustomerGroups;
use GetCandy\Api\Core\Scopes\CustomerGroupScope;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Traits\HasShippingExclusions;
use GetCandy\Api\Core\Collections\Models\Collection;
use GetCandy\Api\Core\Discounts\Models\DiscountCriteriaModel;
use GetCandy\Api\Core\Http\Transformers\Fractal\Products\ProductTransformer;

class Product extends BaseModel
{
    use Assetable,
        HasCustomerGroups,
        HasAttributes,
        HasChannels,
        HasRoutes,
        SoftDeletes,
        Indexable,
        HasShippingExclusions;

    protected $settings = 'products';

    /**
     * The products minimum price.
     *
     * @var int
     */
    public $min_price = 0;

    /**
     * The products maxiumum price.
     *
     * @var int
     */
    public $max_price = 0;

    public $min_price_tax = 0;

    public $max_price_tax = 0;

    protected $dates = ['deleted_at'];

    public $transformer = ProductTransformer::class;

    /**
     * The Hashid Channel for encoding the id.
     * @var string
     */
    protected $hashids = 'product';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'price', 'attribute_data', 'option_data', 'deleted_at',
    ];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CustomerGroupScope);
        static::addGlobalScope(new ChannelScope);
    }

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
     * ].
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
     * Get the attributes associated to the product.
     * @return Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function collections()
    {
        return $this->belongsToMany(Collection::class)->withTimestamps();
    }

    /**
     * Get the related family.
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function family()
    {
        return $this->belongsTo(ProductFamily::class, 'product_family_id');
    }

    /**
     * Get the products page.
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
        return $this->belongsToMany(Category::class, 'product_categories')->withPivot('position');
    }

    public function associations()
    {
        return $this->hasMany(ProductAssociation::class)->whereHas('association');
    }

    public function discounts()
    {
        return $this->morphMany(
            DiscountCriteriaModel::class,
            'eligible'
        )->with('criteria.set.discount');
    }

    public function recommendations()
    {
        return $this->hasMany(ProductRecommendation::class, 'related_product_id');
    }
}
