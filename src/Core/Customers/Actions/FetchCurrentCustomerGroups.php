<?php

namespace GetCandy\Api\Core\Customers\Actions;

use GetCandy;
use GetCandy\Api\Core\Scaffold\AbstractAction;

class FetchCurrentCustomerGroups extends AbstractAction
{
    /**
     * Determine if the user is authorized to make this action.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the action.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Execute the action and return a result.
     *
     * @return mixed
     */
    public function handle()
    {
        $user = $this->user();
        $defaultGroup = FetchDefaultCustomerGroup::run();
        $guestGroups = [$defaultGroup->id];
        if (! $user) {
            return $guestGroups;
        }

        $hubAccess = $user->hasAnyRole(['admin']);

        if (($hubAccess && GetCandy::isHubRequest()) ||
            (! GetCandy::isHubRequest() && ! $hubAccess)
        ) {
            return $user->customer->customerGroups->pluck('id')->toArray();
        }

        return $guestGroups;
    }
}
