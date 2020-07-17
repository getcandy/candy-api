<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\Associations\CreateRequest;
use GetCandy\Api\Http\Requests\Products\Associations\DeleteRequest;
use GetCandy\Api\Http\Resources\Products\ProductAssociationCollection;

class ProductAssociationController extends BaseController
{
    /**
     * Handles the request to update a products associations.
     *
     * @param  string  $product
     * @param  \GetCandy\Api\Http\Requests\Products\Associations\CreateRequest  $request
     * @return \GetCandy\Api\Http\Resources\Products\ProductAssociationCollection
     */
    public function store($product, CreateRequest $request)
    {
        $result = GetCandy::productAssociations()->store($product, $request->all());

        return new ProductAssociationCollection($result);
    }

    /**
     * Handles the request to remove a product association.
     *
     * @param  string  $product
     * @param  \GetCandy\Api\Http\Requests\Products\Associations\DeleteRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($product, DeleteRequest $request)
    {
        GetCandy::productAssociations()->destroy($product, $request->associations);

        return $this->respondWithNoContent();
    }
}
