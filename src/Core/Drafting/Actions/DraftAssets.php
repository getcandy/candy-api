<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class DraftAssets extends AbstractAction
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
        foreach ($this->parent->assets as $asset) {
            $this->draft->assets()->attach(
                $asset->id,
                [
                    'primary' => $asset->pivot->primary,
                    'position' => $asset->pivot->position,
                    'assetable_type' => $asset->pivot->assetable_type,
                ]
            );
        }
        return $this->draft;
    }
}