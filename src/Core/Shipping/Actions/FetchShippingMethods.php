<?php

namespace GetCandy\Api\Core\Shipping\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Shipping\Models\ShippingMethod;
use GetCandy\Api\Http\Resources\Shipping\ShippingMethodCollection;

class FetchShippingMethods extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        $this->paginate = $this->paginate === null ?: $this->paginate;

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
            'per_page' => 'numeric|max:200',
            'paginate' => 'boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $includes = $this->resolveEagerRelations();

        $query = ShippingMethod::with($includes);

        if (! $this->paginate) {
            return $query->get();
        }

        return $query->withCount(
            $this->resolveRelationCounts()
        )->paginate($this->per_page ?? 50);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Routes\Models\Route|Illuminate\Pagination\LengthAwarePaginator  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Http\Resources\Shipping\ShippingMethodCollection
     */
    public function response($result, $request)
    {
        return new ShippingMethodCollection($result);
    }
}
