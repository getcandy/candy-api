<?php

namespace GetCandy\Api\Core\Products\Actions\Versioning;

use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class RestoreProductVariantTiers extends AbstractAction
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
        // Remove all the tiers we're going to re add them.
        $this->draft->tiers()->delete();

        $groups = FetchCustomerGroups::run([
            'paginate' => false,
        ])->pluck('id');

        // We can only restore tiered pricing for customer groups
        // that still exist in the db, we don't really want to assume anything.
        $this->versions->filter(function ($version) use ($groups) {
            return $groups->contains($version->model_data['customer_group_id']);
        })->each(function ($version) {
            $data = collect($version->model_data)->except(['id', 'product_variant_id']);
            $this->draft->tiers()->create($data->toArray());
        });

        return $this->draft;
    }
}
