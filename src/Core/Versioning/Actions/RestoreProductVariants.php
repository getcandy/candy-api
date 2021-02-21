<?php

namespace GetCandy\Api\Core\Versioning\Actions;

use Illuminate\Support\Facades\Log;
use GetCandy\Api\Core\Taxes\Models\Tax;
use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Products\Models\ProductPricingTier;
use GetCandy\Api\Core\Products\Models\ProductCustomerPrice;
use GetCandy\Api\Core\Products\Actions\Versioning\RestoreProductVariantTiers;
use GetCandy\Api\Core\Products\Actions\Versioning\RestoreProductVariantCustomerPricing;

class RestoreProductVariants extends AbstractAction
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
            'versions' => 'required',
            'draft' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        // Just remove existing variants.
        $this->draft->variants()->delete();

        $taxes = Tax::get();

        $variants = $this->versions->map(function ($version) use ($taxes) {
            $data = collect($version->model_data)->except(['id', 'product_id']);

            // Do we have the tax record that exists?
            $taxRecordExists = $taxes->contains('id', $data['tax_id']);

            if (!$taxRecordExists) {
                $data['tax_id'] = $taxes->first(function($tax) {
                    return $tax->default;
                })->id;
            }
            $data['options'] = $data['options'] ?? [];

            $data['product_id'] = $this->draft->id;

            $variant = $this->draft->variants()->create($data->toArray());

            $version->relations->groupBy('versionable_type')
            ->each(function ($versions, $type) use ($variant) {
                $action = null;
                switch ($type) {
                    case ProductPricingTier::class:
                        $action = RestoreProductVariantTiers::class;
                        break;
                    case ProductCustomerPrice::class:
                        $action = RestoreProductVariantCustomerPricing::class;
                        break;
                }
                if (!$action) {
                    Log::error("Unable to restore for {$type}");
                    return;
                }
                (new $action)->run([
                    'versions' => $versions,
                    'draft' => $variant,
                ]);
            });

            return;
        });

        return $this->draft;
    }
}
