<?php

namespace GetCandy\Api\Core\Products\Actions;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Traits\ReturnsJsonResponses;
use GetCandy\Api\Http\Resources\Products\ProductResource;

class FetchProduct extends AbstractAction
{
    use ReturnsJsonResponses;

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
    public function rules(): array
    {
        return [
            'id' => 'integer|required_without_all:encoded_id,sku',
            'encoded_id' => 'string|hashid_is_valid:'.Product::class.'|required_without_all:id,sku',
            'sku' => 'nullable|string',
            'draft' => 'nullable|boolean',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Products\Models\Product|null
     */
    public function handle()
    {
        if ($this->encoded_id && ! $this->sku) {
            $this->id = (new Product())->decodeId($this->encoded_id);
        }

        $query = Product::query()
            ->withCount($this->resolveRelationCounts())
            ->with($this->resolveEagerRelations());

        if ($this->draft) {
            $query->withDrafted();
        }

        if ($this->sku) {
            return $query->whereHas('variants', function ($query) {
                $query->whereSku($this->sku);
            })->first();
        }

        return $query->find($this->id);
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Products\Models\Product|null  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Products\Resources\ProductResource|\Illuminate\Http\JsonResponse
     */
    public function response($result, $request)
    {
        if (! $result) {
            return $this->errorNotFound();
        }

        return (new ProductResource($result))->only($request->fields);
    }
}
