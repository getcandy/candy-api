<?php

namespace GetCandy\Api\Core\Products\Actions\Drafting;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Products\Resources\ProductFamilyResource;
use GetCandy\Api\Core\Attributes\Actions\AttachModelToAttributes;
use GetCandy\Api\Core\Products\Actions\Drafting\UpdateProductVariantCustomerPricing;

class UpdateProductVariants extends AbstractAction
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
            'product' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \GetCandy\Api\Core\Products\Models\ProductFamily
     */
    public function handle()
    {
        $variants = $this->draft->variants()->onlyDrafted()->get();

        foreach ($variants as $incoming) {
            if ($incoming->publishedParent) {
                $parent = $incoming->publishedParent;
                $parent->update($incoming->toArray());

                UpdateProductVariantCustomerPricing::run([
                    'draft' => $incoming,
                    'parent' => $parent,
                ]);

                UpdateProductVariantTiers::run([
                    'draft' => $incoming,
                    'parent' => $parent,
                ]);

                dd(1);
            } else {
                $incoming->update([
                    'product_id' => $this->product->id,
                ]);
            }
        }

        return $this->product->refresh();
    }
}