<?php

namespace GetCandy\Api\Core\Products\Actions\Drafting;

use GetCandy\Api\Core\Attributes\Actions\AttachModelToAttributes;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Products\Resources\ProductFamilyResource;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class UpdateProductAssociations extends AbstractAction
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
        $this->product->associations()
            ->whereNotIn(
                'association_id',
                $this->draft->associations->pluck('association_id')->toArray()
            )->delete();

        foreach ($this->draft->associations as $incoming) {
            // Does this parent already have this association?
            // If so we just need to update the group
            $existing = $this->product->associations->first(function ($assoc) use ($incoming) {
                return $assoc->association_id = $incoming->association_id;
            });
            if ($existing) {
                $existing->update($incoming->toArray());
                continue;
            }
            // If it doesn't exist, reassign the product_id
            $incoming->update([
                'product_id' => $this->product->id,
            ]);
        }

        return $this->product;
    }
}