<?php

namespace GetCandy\Api\Discounts;

use GetCandy\Api\Discounts\Criteria\ProductIn;
use GetCandy\Api\Discounts\Criteria\Coupon;
use TaxCalculator;

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
     * Checks the criteria
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

            if (!$criteria->process($user, $product, $basket)) {
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

        foreach ($basket->lines as $line) {
            $basket->total += $line->current_total;

            if ($line->variant->tax) {
                $tieredPrice = app('api')->productVariants()->getTieredPrice(
                    $line->variant,
                    $line->quantity,
                    $basket->user
                );
                if ($tieredPrice) {
                    $basket->tax += $tieredPrice->tax * $line->quantity;
                } else {
                    // TODO: Move to tax calculator
                    if ($line->variant->tax) {
                        $exVat = $line->current_total / (($line->variant->tax->percentage + 100) / 100);
                        $basket->tax += $line->current_total - $exVat;
                    } else {
                        $basket->tax += 0;
                    }
                }
            }

        }
    }

    public function applyToBasket($discounts, $basket)
    {
        $lines = collect();

        $this->setTotalAndTax($basket);

        // Go through each discount
        foreach ($discounts as $discount) {
            // Go through each set
            foreach ($discount->getCriteria() as $criteria) {
                foreach ($criteria->getSets() as $set) {
                    if ($set instanceof ProductIn) {
                        // Go through each basket line and see which ones apply...
                        foreach ($basket->lines as $line) {
                            $productId = $line->variant->product->id;
                            if ($set->getRealIds()->contains($productId)) {
                                $lines->push($line);
                            }
                        }
                        $discountable = 0;
                        foreach ($lines as $line) {
                            $discountable += $line->total;
                        }

                        $subtotal -= $discountable;

                        foreach ($discount->getRewards() as $reward) {
                            $discountable = $this->applyPercentage($discountable, $reward['value']);
                        }

                        $subtotal += $discountable;

                        break;
                    } elseif ($set instanceof Coupon) {
                        foreach ($discount->getRewards() as $reward) {
                            $basket->total = $this->applyPercentage($basket->total, $reward['value']);
                            $basket->tax = $this->applyPercentage($basket->tax, $reward['value']);
                        }
                    }
                }
            }
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
                'description' => $model->description
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
        $result = ($price / 100) * $amount;
        return round($price - $result, 2);
    }

    protected function applyFixedAmount($price, $amount)
    {
        if ($price > $amount) {
            return round($price - $amount, 2);
        }
        return $price;
    }

    protected function applyToFixedAmount($price, $amount)
    {
        return $amount;
    }
}
