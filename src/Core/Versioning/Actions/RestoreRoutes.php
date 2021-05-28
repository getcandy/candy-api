<?php

namespace GetCandy\Api\Core\Versioning\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class RestoreRoutes extends AbstractAction
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
        // Revove any existing routes for this draft.
        $this->draft->routes()->forceDelete();

        $routes = $this->versions->map(function ($version) {
            return collect($version->model_data)->only(['slug', 'redirect', 'path', 'locale', 'default', 'description']);
        });

        $this->draft->routes()->createMany($routes->toArray());

        return $this->draft;
    }
}
