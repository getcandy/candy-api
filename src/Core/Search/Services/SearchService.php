<?php

namespace GetCandy\Api\Core\Search\Services;

use Elastica\ResultSet;
use League\Fractal\Resource\Collection;
use Elastica\Exception\InvalidException;
use GetCandy\Api\Http\Transformers\Fractal\Products\ProductTransformer;
use GetCandy\Api\Http\Transformers\Fractal\Categories\CategoryTransformer;

class SearchService
{
    protected $types = [
        'product' => ProductTransformer::class,
        'category' => CategoryTransformer::class,
    ];

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
        $ids = [];

        if ($includes) {
            app()->fractal->parseIncludes($includes);
        }

        if ($results->count()) {
            foreach ($results as $r) {
                $ids[] = $r->getSource()['id'];
            }
            $collection = app('api')->{str_plural($type)}()->getSearchedIds($ids, true);
        } else {
            $collection = collect();
        }

        $transformer = new $this->types[$type];

        $resource = new Collection($collection, $transformer);

        $resource->setMeta([
            'sort' => $this->getSort($results),
            'category_page' => (bool) $category,
            'pagination' => ['data' => $this->getPagination($results, $page)],
            'aggregation' => ['data' => $results->getAggregations()],
            'suggestions' => $this->getSuggestions($results),
        ]);

        $data = app()->fractal->createData($resource)->toArray();

        return $data;
    }

    /**
     * Maps the search sorting used to something we can use.
     *
     * @param ResultSet $results
     *
     * @return array
     */
    protected function getSort($results)
    {
        try {
            $params = $results->getQuery()->getParam('sort');
        } catch (InvalidException $e) {
            return;
        }

        $sorting = [];

        foreach ($params as $param) {
            foreach ($param as $key => $value) {
                $sort = $key;
                $order = $value;
                if (is_iterable($value)) {
                    $field = explode('.', $sort);

                    if (! empty($field[1])) {
                        $sort = $field[1].'_'.str_replace('ing', 'e', $field[0]);
                    } else {
                        $sort = $field[0];
                    }
                    $order = $value['order'];
                }
            }
            $sorting[] = [
                'sort' => $sort,
                'order' => $order,
            ];
        }

        return $sorting;
    }

    /**
     * Get the pagination for the results.
     *
     * @param array $results
     *
     * @return array
     */
    protected function getPagination($results, $page)
    {
        $query = $results->getQuery();
        $totalPages = ceil($results->getTotalHits() / $query->getParam('size'));

        $pagination = [
            'total' => $results->getTotalHits(),
            'count' => $results->count(),
            'per_page' => (int) $query->getParam('size'),
            'current_page' => (int) $page,
            'total_pages' => (int) ($totalPages <= 0 ? 1 : $totalPages),
        ];

        return $pagination;
    }

    /**
     * Get the search suggestions.
     *
     * @param ResultSet $results
     *
     * @return array
     */
    protected function getSuggestions($results)
    {
        $suggestions = [];

        foreach ($results->getSuggests() as $field => $item) {
            foreach ($item as $suggestion) {
                if (count($suggestion['options'])) {
                    foreach ($suggestion['options'] as $option) {
                        $suggestions[$field][] = $option;
                    }
                }
            }
        }

        return $suggestions;
    }

    public function getSuggestResults(ResultSet $results, $type = 'product')
    {
        $suggestions = $this->getSuggestions($results);

        if (empty($suggestions['suggest'])) {
            return [];
        }

        $ids = collect($suggestions['suggest'])->map(function ($item) {
            return $item['_id'];
        });

        return app('api')->{str_plural($type)}()->getSearchedIds($ids, true);
    }
}
