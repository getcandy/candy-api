<?php

namespace GetCandy\Api\Core\Search\Services;

use Elastica\ResultSet;
use GetCandy\Api\Core\Products\Services\ProductService;
use GetCandy\Api\Core\Categories\Services\CategoryService;
use GetCandy\Api\Core\Search\Interfaces\SearchResultInterface;

class SearchService
{
    /**
     * The products service.
     *
     * @var ProductService
     */
    protected $products;

    /**
     * The category service.
     *
     * @var CategoryService
     */
    protected $categories;

    /**
     * The search result factory.
     *
     * @var SearchResultInterface
     */
    protected $factory;

    public function __construct(
        SearchResultInterface $factory,
        ProductService $products,
        CategoryService $categories
    ) {
        $this->factory = $factory;
        $this->products = $products;
        $this->categories = $categories;
    }

    /**
     * Gets the search results from the result set.
     *
     * @param ResultSet $results
     * @param string $type
     * @param int $page
     * @param int $perpage
     * @param mixed $includes
     *
     * @return array
     */
    public function getResults(ResultSet $results, $type, $includes = null, $page = 1, $category = false)
    {
        $service = $type == 'product' ? $this->products : $this->categories;

        return $this->factory
            ->include($includes)
            ->type($type)
            ->page($page)
            ->service($service)
            ->category($category)
            ->init($results)
            ->get();
    }
}
