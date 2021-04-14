<?php

namespace GetCandy\Api\Core\Drafting\Actions;

use GetCandy\Api\Core\Scaffold\AbstractAction;

class PublishCustomerGroups extends AbstractAction
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
            $groupData = [
                'visible' => $group->pivot->visible,
            ];
            if (isset($group->pivot->toArray()['purchasable'])) {
                $groupData['purchasable'] = $group->pivot->purchasable;
            }
            return [$group->id => $groupData];
        })->toArray();

        $this->parent->customerGroups()->sync($customerGroups);

        return $this->parent;
    }
}
