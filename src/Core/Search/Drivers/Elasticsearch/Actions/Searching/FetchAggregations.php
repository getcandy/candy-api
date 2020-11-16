<?php

namespace GetCandy\Api\Core\Search\Drivers\Elasticsearch\Actions\Searching;

use GetCandy\Api\Core\Attributes\Actions\FetchFilterableAttributes;
use GetCandy\Api\Core\Search\Drivers\Elasticsearch\Aggregators\Attribute;
use Lorisleiva\Actions\Action;

class FetchAggregations extends Action
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
            'aggregate' => 'array|min:0',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return array|null
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function handle()
    {
        if (! $this->aggregate) {
            return null;
        }

        return FetchFilterableAttributes::run()->map(function ($attribute) {
            return $attribute->handle;
        })->filter(function ($attribute) {
            return in_array($attribute, $this->aggregate);
        })->merge(
            collect(['priceRange', 'category'])
        )->map(function ($attribute) {
            $name = ucfirst(camel_case(str_singular($attribute)));
            $classname = "GetCandy\Api\Core\Search\Drivers\Elastic\Aggregators\\{$name}";
            if (class_exists($classname)) {
                return app()->make($classname);
            }

            return new Attribute($attribute);
        })->toArray();
    }
}
