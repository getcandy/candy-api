<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\FetchCategoryMapping;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\FetchProductMapping;
use GetCandy\Api\Core\Search\Providers\Elastic\Sorts\BasicSort;
use GetCandy\Api\Core\Search\Providers\Elastic\Sorts\NestedSort;
use GetCandy\Api\Core\Search\Providers\Elastic\Sorts\TextSort;
use Lorisleiva\Actions\Action;

class SetSorting extends Action
{
    protected $sorts = [];

    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->sorts = collect($this->sorts);

        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'query' => 'required',
            'type' => 'string',
            'sort' => 'nullable|string',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Elastica\Query
     */
    public function handle()
    {
        $sorts = [];

        if ($this->sort) {
            $sortable = explode('|', $this->sort);
            foreach ($sortable as $sort) {
                $segments = explode('-', $sort);
                $dir = $segments[1] ?? 'asc';
                $field = $segments[0];
                $sorts[$field] = $dir;
            }
        }

        $mapping = $this->type == 'products' ? FetchProductMapping::run() : FetchCategoryMapping::run();

        foreach ($sorts as $field => $dir) {
            $column = $field;

            if ($field == 'min_price' || $field == 'max_price') {
                $field = 'pricing';
            }

            if (empty($mapping[$field])) {
                continue;
            }

            $map = $mapping[$field];
            // If it's a text property, elastic won't sort on it.
            // So lets find any sortable fields we can use...
            if ($map['type'] == 'text') {
                if (empty($map['fields'])) {
                    continue;
                }
                $this->sorts->push(
                    new TextSort($field, 'sortable', $dir)
                );
            } elseif ($map['type'] == 'nested') {
                $this->sorts->push(
                    new NestedSort($field, $column, $dir, 'min')
                );
            } else {
                $sort = new BasicSort($field);
                $sort->setDir($dir);
                $this->sorts->push($sort);
            }
        }

        foreach ($this->sorts as $sort) {
            $this->query->addSort($sort->getMapping($this->user()));
        }

        return $this->query;
    }
}
