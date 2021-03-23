<?php

namespace GetCandy\Api\Core\Drafting\Actions;

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
        $variants = $this->draft->variants()->withDrafted()->get();

        $skus = collect([]);

        foreach ($variants as $incoming) {
            if ($incoming->publishedParent) {
                $parent = $incoming->publishedParent;
                $modelData = collect($incoming->toArray())->except(['id', 'product_id', 'options'])->toArray();
                // We don't want to interact with the accessor so we have to do this.
                // TODO: Remove the options accessor junk
                $modelData['options'] = $incoming->getAttributes()['options'];
                $parent->update($modelData);

                (new PublishProductVariantCustomerPricing)->actingAs($this->user())->run([
                    'draft' => $incoming,
                    'parent' => $parent,
                ]);

                (new PublishProductVariantTiers)->actingAs($this->user())->run([
                    'draft' => $incoming,
                    'parent' => $parent,
                ]);
            } else {
                $incoming->update([
                    'product_id' => $this->parent->id,
                ]);
            }
            $skus->push($incoming->sku);
        }

        // Any skus we dont have, remove from the parent.
        $this->parent->variants()->whereNotIn('sku', $skus->toArray())->delete();

        return $this->parent->refresh();
    }
}
