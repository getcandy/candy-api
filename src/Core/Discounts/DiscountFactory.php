<?php

namespace GetCandy\Api\Core\Discounts;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use GetCandy\Api\Core\Baskets\Models\Basket;

class DiscountFactory implements DiscountInterface
{
    /**
     * The discount models.
     *
     * @var \Illuminate\Support\Collection
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
     * @param mixed $discounts
     * @return DiscountFactory
     */
    public function init(Collection $discounts)
    {
        $this->discounts = $discounts;

        return $this;
    }

    /**
     * Set the basket.
     *
     * @param Basket $basket
     * @return DiscountFactory
     */
    public function setBasket(Basket $basket)
    {
        $this->basket = $basket;

        return $this;
    }

    /**
     * Set the user.
     *
     * @param Model $user
     * @return DiscountFactory
     */
    public function setUser(Model $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get all applied discounts.
     *
     * @return Illuminate\Support\Collection
     */
    public function getApplied()
    {
        foreach ($this->discounts as $index => $discount) {
            $discount->applied = $this->checkCriteria($discount, $user, $basket, $product);
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
     * @param Discount $discount
     * @param mixed $uesr
     * @param Basket $basket
     * @return bool
     */
    public function checkCriteria($discount, $user = null, $basket = null, $product = null)
    {
        foreach ($discount->getCriteria() as $criteria) {
            $fail = 0;
            $pass = 0;

            if (! $criteria->process($user, $product, $basket)) {
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
