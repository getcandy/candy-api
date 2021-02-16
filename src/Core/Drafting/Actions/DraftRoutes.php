<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class DraftRoutes extends AbstractAction
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
        $this->parent->routes->each(function ($parentRoute) {
            $draftRoute = $parentRoute->replicate();
            $draftRoute->element_id = $this->draft->id;
            $draftRoute->element_type = get_class($this->draft);
            $draftRoute->drafted_at = now();
            $draftRoute->draft_parent_id = $parentRoute->id;
            $draftRoute->save();
        });
        return $this->draft;
    }
}