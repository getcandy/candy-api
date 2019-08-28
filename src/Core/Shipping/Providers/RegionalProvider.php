<?php

namespace GetCandy\Api\Core\Shipping\Providers;

use GetCandy\Api\Core\Shipping\Models\ShippingRegion;

class RegionalProvider extends AbstractProvider
{
    /**
     * Calculates the shipping prices available.
     *
     * @param \GetCandy\Api\Core\Orders\Models\Order $order
     * @return ShippingPrice|null
     */
    public function calculate($order)
    {
        $basket = $order->basket;
        $weight = $basket->weight;
        $users = $this->method->users;
        $prices = $this->method->prices;

        $postcode = $this->getPostcodeToCheck($order);

        $prices = $this->method->prices->filter(function ($price) use ($postcode) {
            return $price->zone->regions()->whereRegion($postcode)->exists();
        });

        if (! $prices->count()) {
            // We haven't found a region so see if we have a "catch all"
            // If there is no region, see if we have a "catch all" and use that.
            $prices = $this->method->prices->filter(function ($price) use ($order) {
                return $price->zone->regions->first(function ($region) use ($order) {
                    return $region->region == '*' && $region->country->name == $order->shipping_country;
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

            if (! $item->min_basket || $basket->sub_total >= ($item->min_basket / 100) && $weight >= $item->min_weight) {
                return $item;
            }
        })->sortBy('rate')->first();

        return $price;
    }

    /**
     * Gets the postcode which should be used to check prices.
     *
     * @param \GetCandy\Api\Core\Orders\Models\Order $order
     * @return string
     */
    protected function getPostcodeToCheck($order)
    {
        $postcode = str_replace(' ', '', strtoupper($order->shippingDetails['zip']));

        if (ShippingRegion::whereRegion($postcode)->exists()) {
            return $postcode;
        }

        $postcode = rtrim(substr($postcode, 0, -3), 'a..zA..Z');

        if (ShippingRegion::whereRegion($postcode)->exists()) {
            return $postcode;
        }

        $postcode = rtrim($postcode, '0..9');

        if (ShippingRegion::whereRegion($postcode)->exists()) {
            return $postcode;
        }

        return substr($postcode, 0, 2);
    }
}
