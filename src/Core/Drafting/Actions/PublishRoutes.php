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
        /**
         * We need to determine if we have removed any routes during the draft period
         * and if we have, we need to remove them from the parent.
         */
        $draftIds = $this->draft->routes->pluck('draft_parent_id');
        $existing = $this->parent->routes->pluck('id');

        $this->parent->routes()->whereIn('id', $existing->diff($draftIds))->delete();

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
