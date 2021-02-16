<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class PublishProductVariantTiers extends AbstractAction
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
        foreach ($this->draft->tiers as $incoming) {
            $existing = $this->parent->tiers->first(function ($existing) use ($incoming) {
                return $existing->customer_group_id === $incoming->customer_group_id;
            });
            if ($existing) {
                $existing->update($incoming->toArray());
                $incoming->forceDelete();
                continue;
            }
            $incoming->update([
                'product_variant_id' => $this->parent->id,
            ]);
        }

        return $this->parent;
    }
}
