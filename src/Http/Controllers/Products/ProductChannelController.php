<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Resources\Products\ProductResource;
use GetCandy\Api\Http\Requests\Products\UpdateChannelRequest;
use GetCandy\Api\Core\Products\Services\ProductChannelService;

class ProductChannelController extends BaseController
{
    /**
     * Handles the request to update a products customer groups.
     * @param  string        $product
     * @param  UpdateChannelRequest $request
     * @return mixed
     */
    public function store($product, UpdateChannelRequest $request, ProductChannelService $service)
    {
        $result = $service->store($product, $request->get('channels', []));

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
