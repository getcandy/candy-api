<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class PublishAssets extends AbstractAction
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
        // Detach any assets.
        $this->parent->assets()->detach();

        $this->draft->assets->each(function ($asset) {
            $this->parent->assets()->attach($asset->id, $asset->pivot->only(['position', 'primary', 'assetable_type']));
        });
        // Clean up on Aisle 4
        $this->draft->assets()->detach();

        return $this->parent;
    }
}
