<?php

namespace GetCandy\Api\Core\Products\Actions\Drafting;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class UpdateAssets extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can('manage-drafts');
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
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        // Get the asset ids we want to sync up
        $incomingAssetIds = $this->draft->assets->pluck('id')->toArray();
        $this->parent->assets()->sync($incomingAssetIds);
        // Clean up on Aisle 4
        $this->draft->assets()->detach();

        return $this->parent;
    }
}