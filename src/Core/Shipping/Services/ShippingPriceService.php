<?php

namespace GetCandy\Api\Core\Shipping\Services;

use GetCandy;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroup;
use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Shipping\Models\ShippingPrice;
use GetCandy\Api\Core\Shipping\Models\ShippingRegion;

class ShippingPriceService extends BaseService
{
    public function __construct()
    {
        $this->model = new ShippingPrice();
    }

    /**
     * Create a shipping price.
     *
     * @param  string  $shippingMethodId
     * @param  array  $data
     * @return \GetCandy\Api\Core\Shipping\Models\ShippingPrice
     */
    public function create($shippingMethodId, array $data)
    {
        $method = GetCandy::shippingMethods()->getByHashedId($shippingMethodId);
        $currency = GetCandy::currencies()->getByHashedId($data['currency_id']);
        $zone = GetCandy::shippingZones()->getByHashedId($data['zone_id']);
        $price = new ShippingPrice;
        $price->fill($data);
        $price->method()->associate($method);
        $price->currency()->associate($currency);
        $price->zone()->associate($zone);
        $price->save();

        if (! empty($data['customer_groups'])) {
            $groupData = $this->mapCustomerGroupData($data['customer_groups']['data']);
            $price->customerGroups()->sync($groupData);
        }

        return $price;
    }

    /**
     * Updates a shipping price.
     *
     * @param  string  $id
     * @param  array  $data
     * @return \GetCandy\Api\Core\Shipping\Models\ShippingPrice
     */
    public function update($id, array $data)
    {
        $price = $this->getByHashedId($id);
        $currency = GetCandy::currencies()->getByHashedId($data['currency_id']);

        // event(new AttributableSavedEvent($product));

        if (! empty($data['customer_groups'])) {
            $groupData = $this->mapCustomerGroupData($data['customer_groups']['data']);
            $price->customerGroups()->sync($groupData);
        }

        $price->currency()->associate($currency);
        $price->fill($data);
        $price->save();

        return $price;
    }

    /**
     * Maps customer group data for a model.
     *
     * @param  array  $groups
     * @return array
     */
    protected function mapCustomerGroupData($groups)
    {
        $groupData = [];
        foreach ($groups as $group) {
            $groupModel = FetchCustomerGroup::run([
                'encoded_id' => $group['id'],
            ]);
            $groupData[$groupModel->id] = [
                'visible' => $group['visible'],
            ];
        }

        return $groupData;
    }

    /**
     * Delete a price.
     *
     * @param  string  $id
     * @return bool
     */
    public function delete($id)
    {
        $price = $this->getByHashedId($id);

        $price->customerGroups()->detach();

        return $price->delete();
    }

    /**
     * Estimates shipping prices for a zip and amount.
     *
     * @param  int  $amount
     * @param  string  $zip
     * @param  int  $limit
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function estimate($amount, $zip, $limit = 1)
    {
        $region = $this->getRegionFromZip($zip);
        if (! $region) {
            // Is there a "catch all" region?
            $region = ShippingRegion::where('region', '=', '*')->first();
            if (! $region) {
                return collect();
            }
        }

        // Get the shipping zone regional prices.
        return $region->zone->prices()->whereHas('method', function ($q) {
            return $q->whereType('regional');
        })->get()->groupBy('shipping_method_id')->sortByDesc('min_basket')->first();
    }

    /**
     * Get a region from a zip code.
     *
     * @param  string  $zip
     * @return \GetCandy\Api\Core\Shipping\Models\ShippingRegion
     */
    public function getRegionFromZip($zip)
    {
        $postcode = rtrim(strtoupper($zip));

        $outcode = rtrim(
            rtrim(substr($postcode, 0, -3)),
            'a..zA..Z'
        );

        $strippedOutcode = rtrim($outcode, '0..9');

        if ($region = ShippingRegion::whereRegion($postcode)->first()) {
            return $region;
        }

        if ($region = ShippingRegion::whereRegion($outcode)->first()) {
            return $region;
        }

        return ShippingRegion::whereRegion($strippedOutcode)->first();
    }
}
