<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use GetCandy\Api\Core\Attributes\Actions\FetchAttributes;
use Lorisleiva\Actions\Action;

class SetExcludedFields extends Action
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
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
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Elastica\Query
     */
    public function handle()
    {
        $attributes = FetchAttributes::run();

        $filterable = $attributes
            ->filter(function ($attribute) {
                return ! $attribute->searchable && ! $attribute->filterable || $attribute->filterable;
            })
            ->pluck('handle')
            ->toArray();

        $this->query->setSource([
            'excludes' => $filterable,
        ]);

        return $this->query;
    }
}
