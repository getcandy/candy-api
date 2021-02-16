<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class DraftProductVariants extends AbstractAction
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
        $this->parent->variants->each(function ($parentVariant) {
            $draftVariant = $parentVariant->replicate();

            $draftVariant->product_id = $this->draft->id;
            $draftVariant->drafted_at = now();
            $draftVariant->draft_parent_id = $parentVariant->id;

            $draftVariant->save();

            (new DraftProductVariantCustomerPricing)->actingAs($this->user())->run([
                'draft' => $draftVariant,
                'parent' => $parentVariant,
            ]);

            (new DraftProductVariantTiers)->actingAs($this->user())->run([
                'draft' => $draftVariant,
                'parent' => $parentVariant,
            ]);
        });

        return $this->draft;
    }
}
