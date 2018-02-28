<?php
namespace GetCandy\Api\Discounts\Models;

use GetCandy\Api\Auth\Models\User;
use GetCandy\Api\Scaffold\BaseModel;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Api\Customers\Models\CustomerGroup;

class DiscountCriteriaItem extends BaseModel
{
    protected $fillable = ['type', 'value'];

    protected $hashids = 'main';

    public function set()
    {
        return $this->belongsTo(DiscountCriteriaSet::class, 'discount_criteria_set_id');
    }

    public function saveEligible($type, $id)
    {
        $relation = camel_case(str_plural($type));

        if (method_exists($this, $relation)) {
            $realId = (new $type)->decodedId($id);
            $this->{$relation}()->attach($realId);
        }
    }

    /**
     * Get all of the owning commentable models.
     */
    public function eligibles()
    {
        return $this->morphTo();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function products()
    {
        return $this->morphedByMany(Product::class, 'eligible', 'discount_criteria_models');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function customerGroups()
    {
        return $this->morphedByMany(CustomerGroup::class, 'eligible', 'discount_criteria_models');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphedByMany
     */
    public function users()
    {
        return $this->morphedByMany(User::class, 'eligible', 'discount_criteria_models');
    }
}
