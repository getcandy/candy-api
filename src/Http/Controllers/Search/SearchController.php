<?php

namespace GetCandy\Api\Http\Controllers\Search;

use Illuminate\Http\Request;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Categories\Models\Category;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Search\SearchRequest;
use GetCandy\Api\Http\Transformers\Fractal\Search\SearchSuggestionTransformer;

class SearchController extends BaseController
{
    protected $types = [
        'product' => Product::class,
        'category' => Category::class,
    ];

    /**
     * Performs a search against a type.
     *
     * @param Request $request
     * @param SearchContract $client
     *
     * @return array
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

        $results = app('api')->search()->getResults(
            $results,
            $request->type,
            $request->includes,
            $request->page ?: 1,
            $request->category
        );

        return response($results, 200);
    }

    /**
     * Gets suggested searches
     *
     * @param SearchRequest $request
     * @param SearchContract $client
     * @return void
     */
    public function suggest(SearchRequest $request, SearchContract $client)
    {
        try {
            $results = $client
                ->client()
                ->language(app()->getLocale())
                ->on($request->channel)
                ->against($this->types[$request->type])
                ->user($request->user())
                ->suggest($request->keywords);
        } catch (\Elastica\Exception\Connection\HttpException $e) {
            return $this->errorInternalError($e->getMessage());
        } catch (\Elastica\Exception\ResponseException $e) {
            return $this->errorInternalError($e->getMessage());
        }

        $results = app('api')->search()->getSuggestResults($results, $request->type);

        return $this->respondWithCollection($results, new SearchSuggestionTransformer);
    }
}
