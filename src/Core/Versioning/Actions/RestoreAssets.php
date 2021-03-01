<?php

namespace GetCandy\Api\Core\Versioning\Actions;

use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class RestoreAssets extends AbstractAction
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
        $assets = $this->versions->filter(function ($version) {
            return Asset::whereId($version->versionable_id)->exists();
        })->mapWithKeys(function ($version) {
            $data = collect($version->model_data);

            return [
                $version->versionable_id => $data->only(['position', 'primary']),
            ];
        });

        $this->draft->assets()->sync($assets->toArray());

        return $this->draft;
    }
}
