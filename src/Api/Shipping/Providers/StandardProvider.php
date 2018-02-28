<?php

namespace GetCandy\Api\Shipping\Providers;

class StandardProvider extends AbstractProvider
{
    public function calculate($basket)
    {
        $weight = $basket->weight;
        $total = $basket->total;
        $users = $this->method->users;
        $prices = $this->method->prices;
        $user = $basket->user;

        $price = $prices->filter(function ($item) use ($weight, $total, $user, $users) {
            if ($users->contains($user)) {
                return $item;
            } elseif ($users->count()) {
                return false;
            }
            if ($total > $item->min_basket && $weight >= $item->min_weight) {
                return $item;
            };
        })->sortBy('rate')->first();
        return $price;
    }
}
