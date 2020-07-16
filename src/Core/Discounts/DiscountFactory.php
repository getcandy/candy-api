<?php

namespace GetCandy\Api\Core\Discounts;

use GetCandy;
use GetCandy\Api\Core\Baskets\Models\Basket;
use Illuminate\Database\Eloquent\Model;

class DiscountFactory implements DiscountInterface
{
    /**
     * The discount models.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $discounts;

    /**
     * The basket.
     *
     * @var \GetCandy\Api\Core\Baskets\Models\Basket
     */
    protected $basket;

    /**
     * The user.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $user;

    /**
     * Initialise the factory.
     *
     * @return $this
     */
    public function init()
    {
        // Pull in all the discounts.
        $this->discounts = GetCandy::discounts()->get();

        return $this;
    }

    /**
     * Set the basket.
     *
     * @param  \GetCandy\Api\Core\Baskets\Models\Basket  $basket
     * @return $this
     */
    public function setBasket(Basket $basket)
    {
        $this->basket = $basket;

        return $this;
    }

    /**
     * Set the user.
     *
     * @param  null|\Illuminate\Database\Eloquent\Model  $user
     * @return $this
     */
    public function setUser(Model $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get all applied discounts.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getApplied()
    {
        foreach ($this->discounts as $index => $discount) {
            $factory = GetCandy::discounts()->getFactory($discount);
            $discount->applied = $this->checkCriteria($factory, $this->user, $this->basket);
            if ($discount->stop) {
                break;
            }
        }

        return collect($this->discounts)->filter(function ($discount) {
            return $discount->applied;
        });
    }

    /**
     * Checks the criteria.
     *
     * @param  \GetCandy\Api\Core\Discounts\Discount  $discount
     * @param  null|\Illuminate\Database\Eloquent\Model  $user
     * @param  null|\GetCandy\Api\Core\Baskets\Models\Basket  $basket
     * @return bool
     */
    public function checkCriteria($discount, $user = null, $basket = null)
    {
        foreach ($discount->getCriteria() as $criteria) {
            $fail = 0;
            $pass = 0;

            if (! $criteria->process($user, $basket)) {
                $fail++;
            } else {
                $pass++;
            }

            if ($criteria->scope == 'any' && $pass) {
                return true;
            } elseif ($criteria->scope == 'all' && ($discount->getCriteria()->count() == $pass)) {
                return true;
            }

            return false;
        }
    }
}
