<?php

namespace GetCandy\Api\Core\Products\Observers;

use GetCandy\Api\Core\Search\SearchManager;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Assets\Services\AssetService;

class ProductObserver
{
    /**
     * @var \GetCandy\Api\Core\Assets\Services\AssetService
     */
    protected $assets;

    protected $search;

    public function __construct(AssetService $assets, SearchManager $search)
    {
        $this->assets = $assets;
        $this->search = $search;
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
            $driver = $this->search->with(config('getcandy.search.driver'));
            $driver->delete($product);
        }
    }
}
