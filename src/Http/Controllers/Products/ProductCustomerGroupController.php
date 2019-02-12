<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Products\ProductResource;
use GetCandy\Api\Core\Products\Services\ProductCustomerGroupService;
use GetCandy\Api\Http\Requests\Products\UpdateCustomerGroupsRequest;

class ProductCustomerGroupController extends BaseController
{
    /**
     * Handles the request to update a products customer groups.
     * @param  string        $product
     * @param  StoreCustomerGroupsRequest $request
     * @return mixed
     */
    public function store($product, UpdateCustomerGroupsRequest $request, ProductCustomerGroupService $service)
    {
        $result = $service->store($product, $request->get('groups'));

        return new ProductResource($result);
    }

    /**
     * Handles the request to remove a product association.
     * @param  string        $product
     * @param  DeleteRequest $request
     * @return mixed
     */
    public function destroy($product, DeleteRequest $request)
    {
        $result = app('api')->productAssociations()->destroy($product, $request->associations);
    }
}
