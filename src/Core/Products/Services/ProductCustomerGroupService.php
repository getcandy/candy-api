<?php

namespace GetCandy\Api\Core\Products\Services;

use GetCandy\Api\Core\Scaffold\BaseService;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroup;
use GetCandy\Api\Core\Customers\Services\CustomerGroupService;

class ProductCustomerGroupService extends BaseService
{
    public function __construct()
    {
        $this->model = new Product;
    }

    /**
     * Stores a product association.
     *
     * @param  string  $product
     * @param  array  $data
     * @return \GetCandy\Api\Core\Products\Models\Product
     */
    public function store($product, $groups)
    {
        $product = $this->getByHashedId($product);
        $groupData = [];
        foreach ($groups as $group) {
            $groupModel = FetchCustomerGroup::run([
                'encoded_id' => $group['id'],
            ]);
            $groupData[$groupModel->id] = [
                'visible' => $group['visible'],
                'purchasable' => $group['purchasable'],
            ];
        }
        $product->customerGroups()->sync($groupData);
        $product->load('customerGroups');

        return $product;
    }

    /**
     * Destroys product customer groups.
     *
     * @param  string  $product
     * @return \GetCandy\Api\Core\Products\Models\Product
     */
    public function destroy($product)
    {
        $product = $this->getByHashedId($product);
        $product->customerGroups()->detach();

        return $product;
    }
}
