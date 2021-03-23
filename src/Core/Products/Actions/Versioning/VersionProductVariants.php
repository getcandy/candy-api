<?php

namespace GetCandy\Api\Core\Products\Actions\Versioning;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;

class VersionProductVariants extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-versions');
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'version' => 'required',
            'product' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        // Create our base version.
        foreach ($this->product->variants as $variant) {
            $variantVersion = (new CreateVersion)->actingAs($this->user())->run([
                'model' => $variant,
                'relation' => $this->version,
            ]);
            (new VersionProductVariantTiers)->actingAs($this->user())->run([
                'version' => $variantVersion,
                'variant' => $variant,
            ]);
            (new VersionProductVariantCustomerPricing)->actingAs($this->user())->run([
                'version' => $variantVersion,
                'variant' => $variant,
            ]);
        }

        return $this->version;
    }
}
