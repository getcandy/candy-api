<?php

namespace GetCandy\Api\Core\Products\Actions\Versioning;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;

class VersionProductAssociations extends AbstractAction
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
            'model' => 'required',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        foreach ($this->model->associations as $assoc) {
            (new CreateVersion())->actingAs($this->user())->run([
                'model' => $assoc,
                'relation' => $this->version,
            ]);
        }

        return $this->version;
    }
}
