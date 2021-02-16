

<?php

namespace GetCandy\Api\Core\Products\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Http\Resources\Products\ProductResource;
use GetCandy\Api\Core\Products\Resources\ProductFamilyResource;
use GetCandy\Api\Core\Attributes\Actions\AttachModelToAttributes;

class PublishProduct extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-product');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Products\Models\ProductFamily
     */
    public function handle()
    {

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
        return new ProductResource($result);
    }
}
