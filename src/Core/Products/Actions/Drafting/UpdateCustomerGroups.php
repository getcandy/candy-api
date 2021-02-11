<?php

namespace GetCandy\Api\Core\Products\Actions\Drafting;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class UpdateCustomerGroups extends AbstractAction
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
        $customerGroups = $this->draft->customerGroups->mapWithKeys(function ($group) {
            return [$group->id => [
                'purchasable' => $group->pivot->purchasable,
                'visible' => $group->pivot->visible,
            ]];
        })->toArray();

        $this->parent->customerGroups()->sync($customerGroups);

        return $this->parent;
    }
}