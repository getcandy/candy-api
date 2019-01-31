<?php

namespace GetCandy\Api\Core\Products\Models;

use PriceCalculator;
use GetCandy\Api\Core\Scaffold\BaseModel;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Builder;

class ProductPricingTier extends BaseModel
{
    protected $fillable = [
        'customer_group_id',
        'product_variant_id',
        'lower_limit',
        'price',
    ];

        /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        $roles = app('api')->roles()->getHubAccessRoles();

        if ($user = app('auth')->user()) {
            $groups = $user->groups->pluck('id')->toArray();
        } else {
            $groups = [app('api')->customerGroups()->getGuestId()];
        }

        $user = app('auth')->user();

        static::addGlobalScope('available', function (Builder $builder) use ($user, $groups, $roles) {
            if (! $user || ! $user->hasAnyRole($roles)) {
                $builder->whereHas('group', function ($query) use ($groups) {
                    $query->whereIn('id', $groups);
                });
            }
        });
    }

    public function scopeInGroups($query, $groups)
    {
        return $query->whereHas('group', function ($q) use ($groups) {
            $q->whereIn('id', $groups);
        });
    }

    /**
     * Get the total cost attribute.
     *
     * @return int
     */
    public function getTotalCostAttribute()
    {
        return app()->getInstance()->make(PriceCalculator::class)->get($this->price)->total_cost;
    }

    /**
     * Get the total cost attribute.
     *
     * @return int
     */
    public function getTotalTaxAttribute()
    {
        return app()->getInstance()->make(PriceCalculator::class)->get($this->price)->total_tax;
    }

    /**
     * The Hashid Channel for encoding the id.
     * @var string
     */
    protected $hashids = 'product_family';

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function group()
    {
        return $this->belongsTo(CustomerGroup::class, 'customer_group_id');
    }


    protected function getCustomerGroups()
    {
        // If there is a user, get their groups.
        if ($user = app('auth')->user()) {
            return $user->groups->pluck('id')->toArray();
        } else {
            return [app('api')->customerGroups()->getGuestId()];
        }
    }
}
