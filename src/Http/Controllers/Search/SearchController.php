<?php

namespace GetCandy\Api\Http\Controllers\Search;

use Illuminate\Http\Request;
use GetCandy\Api\Core\Search\SearchContract;
use GetCandy\Api\Http\Controllers\BaseController;
use GetCandy\Api\Http\Requests\Search\SearchRequest;
use GetCandy\Api\Core\Channels\Services\ChannelService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GetCandy\Api\Core\Categories\Services\CategoryService;
use GetCandy\Api\Http\Transformers\Fractal\Search\SearchSuggestionTransformer;

class SearchController extends BaseController
{
    /**
     * The channel service.
     *
     * @var ChannelService
     */
    protected $channels;

    public function __construct(ChannelService $channels, CategoryService $categories)
    {
        $this->channels = $channels;
        $this->categories = $categories;
    }

    /**
     * Performs a search against a type.
     *
     * @param Request $request
     * @param SearchContract $client
     *
     * @return array
     */
    public function search(SearchRequest $request, SearchContract $search)
    {
        // Get channel
        $defaultChannel = $this->channels->getDefaultRecord();
        $channel = $request->channel ?: $defaultChannel ? $defaultChannel->handle : null;

        try {
            $category = $this->categories->getByHashedId($request->category);
        } catch (ModelNotFoundException $e) {
            $category = null;
        }

        if ($request->current_page) {
            $page = $request->current_page;
        } else {
            $page = $request->page;
        }

        // Get our filterable attributes.
        $filterable = app('api')->attributes()->getFilterable()->pluck('handle')->toArray();

        try {
            $results = $search
                ->client()
                ->language(app()->getLocale())
                ->on($channel)
                ->against($request->type)
                ->user($request->user())
                ->search(
                    $request->keywords,
                    $category,
                    $request->only($filterable),
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
            $request->category,
            $request->user()
        );

        return response($results, 200);
    }

    /**
     * Gets suggested searches.
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
