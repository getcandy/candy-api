<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\Associations\CreateRequest;
use GetCandy\Api\Http\Requests\Products\Associations\DeleteRequest;
use GetCandy\Api\Http\Resources\Products\ProductAssociationCollection;

class ProductAssociationController extends BaseController
{
    /**
     * Handles the request to update a products attributes.
     * @param  string        $product
     * @param  UpdateAttributesRequest $request
     * @return mixed
     */
    public function store($product, CreateRequest $request)
    {
        $result = app('api')->productAssociations()->store($product, $request->all());

        return new ProductAssociationCollection($result);
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

        return $this->responseWithNoContent();
    }
}
