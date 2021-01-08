<?php

namespace GetCandy\Api\Core\Products\Models;

use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Traits\HasAttributes;
use GetCandy\Api\Core\Traits\Lockable;

class ProductVariant extends BaseModel
{
    use HasAttributes, Lockable;

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
        'options',
        'price',
        'incoming',
        'sku',
        'stock',
        'backorder',
        'incoming',
        'unit_qty',
        'min_qty',
        'max_qty',
        'min_batch',
    ];

    protected $pricing;

    /**
     * Return the product relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    /**
     * Return the product relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function availableProduct()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Return the basket lines.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function basketLines()
    {
        return $this->hasMany(BasketLine::class);
    }

    /**
     * Get the variant name attribute.
     *
     * @return string
     */
    public function getNameAttribute()
    {
        //TODO: Figure out a more dynamic way to do this
        $name = '';
        $localeUsed = 'en';
        $locale = app()->getLocale();
        $i = 0;

        foreach ($this->options as $handle => $option) {
            if (! empty($option[$locale])) {
                $localeUsed = $locale;
            }
            $name .= $option[$localeUsed].($i == count($this->options) ? ', ' : '');
        }

        return $name;
    }

    public function getOptionsAttribute($val)
    {
        $values = [];
        $option_data = $this->product ? $this->product->option_data : [];

        foreach (json_decode($val, true) as $option => $value) {
            if (! empty($data = $option_data[$option])) {
                $values[$option] = $data['options'][$value]['values'] ?? [
                    'en' => null,
                ];
            }
        }

        return $values;
    }

    public function setOptionsAttribute($val)
    {
        $options = [];

        if (! $this->id) {
            foreach ($val as $option => $value) {
                if (is_array($value)) {
                    $value = reset($value);
                }
                $options[str_slug($option)] = str_slug($value);
            }
            $this->attributes['options'] = json_encode($options);
        } else {
            $this->attributes['options'] = $val;
        }
    }

    public function image()
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function customerPricing()
    {
        return $this->hasMany(ProductCustomerPrice::class);
    }

    public function tiers()
    {
        return $this->hasMany(ProductPricingTier::class);
    }
}
