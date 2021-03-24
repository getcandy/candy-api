<?php

namespace GetCandy\Api\Core\Categories\Observers;

use GetCandy\Api\Core\Assets\Services\AssetService;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Core\Search\SearchManager;

class CategoryObserver
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
     * @param  \GetCandy\Api\Core\Categories\Models\Category $category
     * @return void
     */
    public function deleted(Category $category)
    {
        $category->channels()->detach();
        $category->assets()->detach();
        $category->routes()->forceDelete();
        $driver = $this->search->with(config('getcandy.search.driver'));
        $driver->delete($category);
    }
}
