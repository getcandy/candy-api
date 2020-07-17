<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Core\Products\Services\ProductCustomerGroupService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\UpdateCustomerGroupsRequest;
use GetCandy\Api\Http\Resources\Products\ProductResource;

class ProductCustomerGroupController extends BaseController
{
    /**
     * Handles the request to update a product's customer groups.
     *
     * @param  string  $product
     * @param  \GetCandy\Api\Http\Requests\Products\UpdateCustomerGroupsRequest  $request
     * @param  \GetCandy\Api\Core\Products\Services\ProductCustomerGroupService  $service
     * @return \GetCandy\Api\Http\Resources\Products\ProductResource
     */
    public function store($product, UpdateCustomerGroupsRequest $request, ProductCustomerGroupService $service)
    {
        $result = $service->store($product, $request->get('groups'));

        return new ProductResource($result);
    }

    /**
     * Handles the request to remove a product association.
     *
     * @param  string  $product
     * @param  mixed $request (?)
     * @return \Illuminate\Http\Response
     */
    public function destroy($product, DeleteRequest $request)
    {
        GetCandy::productAssociations()->destroy($product, $request->associations);

        return $this->respondWithNoContent();
    }
}
