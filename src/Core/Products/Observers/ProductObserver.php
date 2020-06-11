<?php

namespace GetCandy\Api\Core\Products\Observers;

use GetCandy\Api\Core\Assets\Services\AssetService;
use GetCandy\Api\Core\Products\Models\Product;

class ProductObserver
{
    /**
     * @var \GetCandy\Api\Core\Assets\Services\AssetService
     */
    protected $assets;

    public function __construct(AssetService $assets)
    {
        $this->assets = $assets;
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \GetCandy\Api\Core\Products\Models\Product  $product
     * @return void
     */
    public function deleted(Product $product)
    {
        if ($product->isForceDeleting()) {
            $product->channels()->detach();
            $product->collections()->detach();
            $product->assets()->detach();
            $product->variants()->forceDelete();
            $product->categories()->detach();
            $product->routes()->forceDelete();
            $product->recommendations()->forceDelete();
        }
    }
}
