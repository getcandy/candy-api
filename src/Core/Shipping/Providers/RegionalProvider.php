<?php

namespace GetCandy\Api\Core\Shipping\Providers;

class RegionalProvider extends AbstractProvider
{
    public function calculate($order)
    {
        $basket = $order->basket;
        $weight = $basket->weight;
        $users = $this->method->users;
        $prices = $this->method->prices;

        // Only get prices for this region.
        $prices = $this->method->prices->filter(function ($price) use ($order) {
            $region = $price->zone->regions->first(function ($region) use ($order) {
                $postcode = strtoupper($order->shippingDetails['zip']);
                $outcode = rtrim(substr($postcode, 0, -3));
                $strippedOutcode = rtrim($outcode, '0..9');

                return $postcode == $region->region ||
                    $region->region == $outcode ||
                    $region->region == $strippedOutcode;
            });

            return (bool) $region;
        });

        if (! $prices->count()) {
            return false;
        }

        $user = $basket->user;
        $price = $prices->filter(function ($item) use ($weight, $basket, $user, $users, $order) {
            if ($users->contains($user)) {
                return $item;
            } elseif ($users->count()) {
                return false;
            }
            if (($basket->sub_total * 100) > $item->min_basket && $weight >= $item->min_weight) {
                return $item;
            }
        })->sortBy('rate')->first();

        return $price;
    }
}
