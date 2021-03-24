<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class PublishRoutes extends AbstractAction
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
        foreach ($this->draft->routes as $route) {
            // dd($route->publishedParent);
            if ($route->publishedParent) {
                $route->publishedParent->update(
                    $route->only(['default', 'redirect', 'slug', 'language_id', 'description'])
                );
                $route->forceDelete();
            // dd($route);
            } else {
                $route->update([
                    'element_id' => $this->parent->id,
                ]);
            }
        }

        return $this->parent;
    }
}
