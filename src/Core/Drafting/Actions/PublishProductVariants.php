<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class PublishProductVariants extends AbstractAction
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
     * @return \GetCandy\Api\Core\Products\Models\Product
     */
    public function handle()
    {
        $variants = $this->draft->variants()->onlyDrafted()->get();

        foreach ($variants as $incoming) {
            if ($incoming->publishedParent) {
                $parent = $incoming->publishedParent;
                $parent->update($incoming->toArray());

                PublishProductVariantCustomerPricing::run([
                    'draft' => $incoming,
                    'parent' => $parent,
                ]);

                PublishProductVariantTiers::run([
                    'draft' => $incoming,
                    'parent' => $parent,
                ]);
            } else {
                $incoming->update([
                    'product_id' => $this->parent->id,
                ]);
            }
        }

        return $this->parent->refresh();
    }
}