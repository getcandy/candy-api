<?php

namespace GetCandy\Api\Core\Shipping\Services;

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
     * @param array $data
     *
     * @return ShippingPrice
     */
    public function create($shippingMethodId, array $data)
    {
        $method = app('api')->shippingMethods()->getByHashedId($shippingMethodId);
        $currency = app('api')->currencies()->getByHashedId($data['currency_id']);
        $price = new ShippingPrice;
        $price->fill($data);
        $price->method()->associate($method);
        $price->currency()->associate($currency);
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
     * @param string $id
     * @param array $data
     *
     * @return ShippingPrice
     */
    public function update($id, array $data)
    {
        $price = $this->getByHashedId($id);
        $currency = app('api')->currencies()->getByHashedId($data['currency_id']);

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
     * @param  array $groups
     * @return array
     */
    protected function mapCustomerGroupData($groups)
    {
        $groupData = [];
        foreach ($groups as $group) {
            $groupModel = app('api')->customerGroups()->getByHashedId($group['id']);
            $groupData[$groupModel->id] = [
                'visible' => $group['visible'],
            ];
        }

        return $groupData;
    }

    /**
     * Delete a price.
     *
     * @param string $id
     *
     * @return bool
     */
    public function delete($id)
    {
        $price = $this->getByHashedId($id);

        return $price->delete();
    }

    /**
     * Estimates shipping prices for a zip and amount.
     *
     * @param int $amount
     * @param string $zip
     * @return void
     */
    public function estimate($amount, $zip, $limit = 1)
    {
        $postcode = strtoupper($zip);
        $outcode = rtrim(substr($postcode, 0, -3));
        $strippedOutcode = rtrim($outcode, '0..9');

        // Get region.
        $region = ShippingRegion::where('region', '=', $postcode)
            ->orWhere('region', '=', $outcode)
            ->orWhere('region', '=', $strippedOutcode)
            ->first();

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
}
