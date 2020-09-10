<?php

namespace GetCandy\Api\Core\Products\Actions;

use GetCandy\Api\Core\Attributes\Actions\AttachModelToAttributes;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Products\Resources\ProductFamilyResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class CreateProductFamily extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-product-families');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|unique:product_families,name',
            'attribute_ids' => 'array',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Products\Models\ProductFamily
     */
    public function handle()
    {
        $productFamily = ProductFamily::create($this->validated());

        if ($this->attribute_ids) {
            AttachModelToAttributes::run([
                'model' => $productFamily,
                'attribute_ids' => $this->attribute_ids,
            ]);
        }

        return $productFamily->load($this->resolveEagerRelations());
    }

    /**
     * Returns the response from the action.
     *
     * @param   \GetCandy\Api\Core\Products\Models\ProductFamily  $result
     * @param   \Illuminate\Http\Request  $request
     *
     * @return  \GetCandy\Api\Core\Products\Resources\ProductFamilyResource
     */
    public function response($result, $request)
    {
        return new ProductFamilyResource($result);
    }
}
