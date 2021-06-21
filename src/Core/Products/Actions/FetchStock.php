<?php

namespace GetCandy\Api\Core\Products\Actions;

use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchStock extends AbstractAction
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
            'variant_id' => 'required_without:sku|hashid_is_valid:product_variants',
            'sku' => 'required_without:variant_id',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Products\Models\ProductFamily
     */
    public function handle()
    {
        if ($this->sku) {
            $variant = ProductVariant::whereSku($this->sku)->first();
        }

        if ($this->variant_id) {
            $realId = (new ProductVariant)->decodeId(
                $this->variant_id
            );
            $variant = ProductVariant::find($realId);
        }

        // Get reserved stock.
        $reserved = FetchReservedStock::run([
            'sku' => $variant->sku,
        ]);

        return $variant->stock - $reserved;
    }
}
