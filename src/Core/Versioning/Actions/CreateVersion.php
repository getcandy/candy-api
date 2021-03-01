<?php

namespace GetCandy\Api\Core\Versioning\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;
use NeonDigital\Versioning\Version;

class CreateVersion extends AbstractAction
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
            'originator' => 'nullable',
            'model' => 'required',
            'model_data' => 'nullable|array',
            'relation' => 'nullable',
        ];
    }

    /**
     * Execute the action and return a result.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle()
    {
        $version = new Version;
        $version->user_id = $this->user()->id;
        $version->versionable_type = get_class($this->model);
        $version->versionable_id = $this->originator->id ?? $this->model->id;

        if ($this->relation) {
            $version->relation_id = $this->relation->id;
        }

        $attributes = $this->model_data ?: $this->model->getAttributes();

        if (! empty($attributes['attribute_data']) && is_string($attributes['attribute_data'])) {
            $attributes['attribute_data'] = json_decode($attributes['attribute_data'], true);
        }

        $version->model_data = json_encode($attributes);
        $version->save();

        return $version;
    }
}
