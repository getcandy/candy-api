<?php

namespace GetCandy\Api\Core\Versioning\Actions;

use GetCandy\Api\Core\Customers\Actions\FetchCustomerGroups;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class RestoreCustomerGroups extends AbstractAction
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
        // Get all the channels that exist in the database.
        $groups = FetchCustomerGroups::run([
            'paginate' => false,
        ])->pluck('id');

        // Only try and restore channels that exist in the database.
        $this->versions->filter(function ($version) use ($groups) {
            return $groups->contains($version->versionable_id);
        })->each(function ($version) {
            $this->draft->customerGroups()->updateExistingPivot(
                $version->versionable_id,
                collect($version->model_data)->only(['visible', 'purchasable'])->toArray()
            );
        });

        return $this->draft;
    }
}
