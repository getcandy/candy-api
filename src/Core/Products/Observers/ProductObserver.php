<?php

namespace GetCandy\Api\Core\Products\Observers;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Assets\Services\AssetService;

class ProductObserver
{
    /**
     * The asset server
     *
     * @var AssetService
     */
    protected $assets;

    public function __construct(AssetService $assets)
    {
        $this->assets = $assets;
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\User  $user
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