<?php

namespace GetCandy\Api\Http\Controllers\Products;

use Illuminate\Http\Request;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Assets\UploadRequest;
use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;

class ProductAssetController extends BaseController
{
    /**
     * Gets all assets for a product.
     * @param  int  $id
     * @param  Request $request
     * @return array|\Illuminate\Http\Response
     */
    public function index($id, Request $request)
    {
        $product = app('api')->products()->getByHashedId($id);
        $assets = app('api')->assets()->getAssets($product, $request->all());

        return $this->respondWithCollection($assets, new AssetTransformer);
    }

    /**
     * Uploads an asset for a product.
     * @param  int        $id
     * @param  UploadRequest $request
     * @return array|\Illuminate\Http\Response
     */
    public function upload($id, UploadRequest $request)
    {
    }
}
