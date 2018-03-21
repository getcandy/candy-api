<?php

namespace GetCandy\Http\Controllers\Api\Search;

use GetCandy\Api\Categories\Models\Category;
use GetCandy\Api\Products\Models\Product;
use GetCandy\Http\Controllers\Api\BaseController;
use GetCandy\Http\Requests\Api\Search\SearchRequest;
use GetCandy\Search\SearchContract;
use Illuminate\Http\Request;

class SearchController extends BaseController
{
    protected $types = [
        'product' => Product::class,
        'category' => Category::class
    ];

    /**
     * Performs a search against a type
     *
     * @param Request $request
     * @param SearchContract $client
     *
     * @return Array
     */
    public function search(SearchRequest $request, SearchContract $client)
    {
        if (empty($this->types[$request->type])) {
            return $this->errorWrongArgs('Invalid type');
        }

        if ($request->current_page) {
            $page = $request->current_page;
        } else {
            $page = $request->page;
        }

        try {
            $results = $client
                ->client()
                ->language(app()->getLocale())
                ->on($request->channel)
                ->against($this->types[$request->type])
                ->user($request->user())
                ->search(
                    $request->keywords,
                    $request->category,
                    $request->filters,
                    $request->sort_by ?: [],
                    $page ?: 1,
                    $request->per_page ?: 10
                );
        } catch (\Elastica\Exception\Connection\HttpException $e) {
            return $this->errorInternalError($e->getMessage());
        } catch (\Elastica\Exception\ResponseException $e) {
            return $this->errorInternalError($e->getMessage());
        }

        // clock()->startEvent('results', 'Getting Search Results');

        $results = app('api')->search()->getResults(
            $results,
            $request->type,
            $request->includes,
            $request->page ? : 1,
            $request->category
        );

        return response($results, 200);
    }
}
