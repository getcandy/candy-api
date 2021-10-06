<?php

namespace GetCandy\Api\Core\Versioning\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class VersionCategories extends AbstractAction
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
        foreach ($this->model->categories as $category) {
            (new CreateVersion())->actingAs($this->user())->run([
                'model' => $category,
                'model_data' => $category->pivot->only(['position']),
                'relation' => $this->version,
            ]);
        }

        return $this->version;
    }
}
