<?php

namespace GetCandy\Api\Http\Controllers\Products;

use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Assets\UploadRequest;
use GetCandy\Api\Http\Transformers\Fractal\Assets\AssetTransformer;
use Illuminate\Http\Request;

class ProductAssetController extends BaseController
{
    /**
     * Gets all assets for a product.
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function index($id, Request $request)
    {
        $product = app('api')->products()->getByHashedId($id);
        $assets = app('api')->assets()->getAssets($product, $request->all());

        return $this->respondWithCollection($assets, new AssetTransformer);
    }

    public function attach($productId, Request $request)
    {
        $product = app('api')->products()->getByHashedId($productId, true);
        $asset = app('api')->assets()->getByHashedId($request->asset_id);

        if (! $asset || ! $product) {
            return $this->errorNotFound();
        }

        $product->assets()->attach($asset, [
            'primary' => ! $product->assets()->images()->exists(),
            'assetable_type' => get_class($product),
            'position' => $request->position ?: $product->assets()->count() + 1,
        ]);

        return $this->respondWithNoContent();
    }

    /**
     * Uploads an asset for a product.
     *
     * @param  int  $id
     * @param  \GetCandy\Api\Http\Requests\Assets\UploadRequest  $request
     * @return void
     */
    public function upload($id, UploadRequest $request)
    {
    }
}
