<?php

namespace GetCandy\Api\Core\Products\Actions;

use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Products\Resources\ProductFamilyCollection;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchProductFamilies extends AbstractAction
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

        $query = ProductFamily::with($includes);

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
     * @param   \GetCandy\Api\Core\Products\Models\ProductFamily|Illuminate\Pagination\LengthAwarePaginator  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Languages\Resources\ProductFamilyCollection
     */
    public function response($result, $request)
    {
        return new ProductFamilyCollection($result);
    }
}
