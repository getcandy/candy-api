<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy;
use GetCandy\Api\Core\Products\Services\ProductChannelService;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Products\UpdateChannelRequest;
use GetCandy\Api\Http\Resources\Products\ProductResource;

class ProductChannelController extends BaseController
{
    /**
     * Handles the request to update a product's channels.
     *
     * @param  string  $product
     * @param  \GetCandy\Api\Http\Requests\Products\UpdateChannelRequest  $request
     * @param  \GetCandy\Api\Core\Products\Services\ProductChannelService  $service
     * @return \GetCandy\Api\Http\Resources\Products\ProductResource
     */
    public function store($product, UpdateChannelRequest $request, ProductChannelService $service)
    {
        $result = $service->store($product, $request->get('channels', []));

        return new ProductResource($result);
    }

    /**
     * Handles the request to remove a product's channel.
     *
     * @param  string  $product
     * @param  mixed  $request (?)
     * @return void
     */
    public function destroy($product, DeleteRequest $request)
    {
        GetCandy::productAssociations()->destroy($product, $request->associations);

        return $this->respondWithNoContent();
    }
}
