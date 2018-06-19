<?php

namespace GetCandy\Api\Core\Discounts;

use GetCandy\Api\Core\Discounts\Criteria\Coupon;
use GetCandy\Api\Core\Discounts\Criteria\ProductIn;

class Factory
{
    protected $rewards = [];

    public function getApplied($discounts, $user, $product = null, $basket = null, $area = 'catalog')
    {
        foreach ($discounts as $index => $discount) {
            $discount->applied = $this->checkCriteria($discount, $user, $basket, $product);
            if ($discount->stop) {
                break;
            }
        }

        return collect($discounts)->filter(function ($discount) {
            return $discount->applied;
        });
    }

    /**
     * Checks the criteria.
     *
     * @param Discount $discount
     * @param mixed $uesr
     * @param Basket $basket
     * @return void
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
            } else {
                return false;
            }
        }
    }

    protected function setTotalAndTax($basket)
    {
        $basket->subTotal = 0;
        $basket->tax = 0;

        foreach ($basket->lines as $line) {
            $basket->subTotal += $line->currentTotal;

            $tieredPrice = app('api')->productVariants()->getTieredPrice(
                $line->variant,
                $line->quantity,
                $basket->user
            );

            if ($tieredPrice) {
                $basket->tax += ($tieredPrice->tax / $line->variant->unit_qty) * $line->quantity;
            } else {
                if ($line->current_tax) {
                    $basket->tax += ($line->variant->taxTotal / $line->variant->unit_qty) * $line->quantity;
                } else {
                    $basket->tax += 0;
                }
            }
        }

        $basket->total = $basket->subTotal + $basket->tax;
    }

    public function applyToBasket($discounts, $basket)
    {
        $lines = collect();

        $this->setTotalAndTax($basket);

        $percentage = 0;
        $fixedAmount = 0;
        $freeshipping = false;

        // Go through each discount
        foreach ($discounts->sortBy('priority') as $discount) {
            // Go through each set
            foreach ($discount->getCriteria() as $criteria) {
                foreach ($criteria->getSets() as $set) {
                    if ($set instanceof ProductIn) {
                    } elseif ($set instanceof Coupon) {
                        foreach ($discount->getRewards() as $reward) {
                            switch ($reward['type']) {
                                case 'percentage':
                                    $percentage += $reward['value'];
                                    break;
                                case 'fixed_amount':
                                    $fixedAmount = $reward['value'];
                                    break;
                                case 'free_shipping':
                                    $freeshipping = true;
                                    break;
                                default:
                                    // Do nothing
                                    break;
                            }
                        }
                    }
                }
            }
            if ($discount->getModel()->stop_rules) {
                break;
            }
        }

        foreach ($basket->lines as $line) {
            if (! $line->shipping) {
                $line->discount = $line->currentTotal * ($percentage / 100);
            }
        }

        if ($freeshipping) {
            $basket->freeShipping = true;
        }

        return $basket;
    }

    public function apply($discounts, $product)
    {
        $product->discounts = [];

        $labels = [];

        foreach ($discounts as $index => $discount) {
            $model = $discount->getModel();

            $labels[] = [
                'name' => $model->name,
                'description' => $model->description,
            ];

            foreach ($discount->getRewards() as $reward) {
                foreach ($product->variants as $variant) {
                    $variant->original_price = $variant->price;
                    switch ($reward['type']) {
                        case 'percentage_amount':
                            $variant->price = $this->applyPercentage($variant->price, $reward['value']);
                            break;
                        case 'fixed_amount':
                            $variant->price = $this->applyFixedAmount($variant->price, $reward['value']);
                            break;
                        case 'to_fixed_price':
                            $variant->price = $this->applyToFixedAmount($variant->price, $reward['value']);
                            break;
                        default:
                            //
                    }
                }
            }
        }

        $product->setAttribute('discounts', $labels);
    }

    protected function applyPercentage($price, $amount)
    {
        $result = $price * ($amount / 100);

        return $price - $result;
    }

    protected function applyFixedAmount($price, $amount)
    {
        if ($price > $amount) {
            return $price - $amount;
        }

        return $price;
    }

    protected function applyToFixedAmount($price, $amount)
    {
        return $amount;
    }
}
