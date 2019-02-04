<?php

namespace GetCandy\Api\Core\Shipping\Providers;

use GetCandy\Api\Core\Taxes\TaxCalculator;

class StandardProvider extends AbstractProvider
{
    public function calculate($order)
    {
        $basket = $order->basket;
        $weight = $basket->weight;
        $total = $basket->total_cost;
        $users = $this->method->users;
        $prices = $this->method->prices;
        $user = $basket->user;

        $price = $prices->filter(function ($item) use ($weight, $total, $user, $users) {
            if ($users->contains($user)) {
                return $item;
            } elseif ($users->count()) {
                return false;
            }
            $withTax = app()->getInstance()->make(TaxCalculator::class)->amount($item->min_basket);

            if ($total > (($item->min_basket + $withTax) / 100) && $weight >= $item->min_weight) {
                return $item;
            }
        })->sortBy('rate')->first();

        return $price;
    }
}
