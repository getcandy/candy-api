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
            $categories = $this->categories->getByHashedIds(
                explode(':', $request->category)
            );
        } catch (ModelNotFoundException $e) {
            $categories = null;
        }

        if ($request->current_page) {
            $page = $request->current_page;
        } else {
            $page = $request->page;
        }

        // Get our filterable attributes.
        $filterable = app('api')->attributes()->getFilterable()->pluck('handle')->toArray();
        $filterable[] = 'price';

        try {
            $results = $search
                ->client()
                ->language(app()->getLocale())
                ->on($channel)
                ->against($request->get('search_type', 'product'))
                ->user($request->user())
                ->categories($categories)
                ->filters($request->only($filterable))
                ->sorting($request->sort)
                ->pagination($page ?: 1, $request->per_page ?: 30)
                ->keywords($request->keywords)
                ->search((bool) $request->get('rank', true));
        } catch (\Elastica\Exception\Connection\HttpException $e) {
            return $this->errorInternalError($e->getMessage());
        } catch (\Elastica\Exception\ResponseException $e) {
            return $this->errorInternalError($e->getMessage());
        }


        $results = app('api')->search()->getResults(
            $results,
            $request->get('search_type', 'product'),
            $request->include,
            $page ?: 1,
            $request->category,
            $request->user(),
            $request->ids_only ?: false
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

    /**
     * Handle the request to do an SKU search.
     *
     * @param Request $request
     * @param SearchContract $client
     * @return json
     */
    public function sku(Request $request, SearchContract $client)
    {
        $this->validate($request, [
            'sku' => 'required|min:3',
        ]);

        $results = $client->client()
            ->on($request->channel)
            ->against('product')
            ->searchSkus($request->sku, $request->get('per_page', 10));

        return response()->json([
            'data' => $results,
        ]);
    }
}
