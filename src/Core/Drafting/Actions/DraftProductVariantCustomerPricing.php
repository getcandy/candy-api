<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class DraftProductVariantCustomerPricing extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-products');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'draft' => 'required',
            'parent' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Products\Models\ProductVariant
     */
    public function handle()
    {
        $this->draft->customerPricing()->createMany(
            $this->parent->customerPricing->map(function ($groupPrice) {
                return $groupPrice->only([
                    'customer_group_id',
                    'tax_id',
                    'price',
                    'compare_at_price',
                ]);
            })
        );

        return $this->draft;
    }
}