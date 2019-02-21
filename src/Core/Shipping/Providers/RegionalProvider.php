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

            // We haven't found a region so see if we have a "catch all"
            // If there is no region, see if we have a "catch all" and use that.
            $prices = $this->method->prices->filter(function ($price) use ($order) {
                return $price->zone->regions->first(function ($region) use ($order) {
                    return $region->region == '*';
                });
            });

            if (! $prices->count()) {
                return false;
            }
        }

        $user = $basket->user;
        $price = $prices->filter(function ($item) use ($weight, $basket, $user, $users, $order) {
            if ($users->contains($user)) {
                return $item;
            } elseif ($users->count()) {
                return false;
            }

            if (! $item->min_basket || $basket->sub_total > ($item->min_basket / 100) && $weight >= $item->min_weight) {
                return $item;
            }
        })->sortBy('rate')->first();

        return $price;
    }
}
