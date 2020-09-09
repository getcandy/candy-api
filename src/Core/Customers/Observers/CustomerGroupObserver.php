<?php

namespace GetCandy\Api\Core\Customers\Observers;

use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class CustomerGroupObserver
{
    /**
     * Handle the Channel "updated" event.
     *
     * @param  \GetCandy\Api\Core\Customers\Models\CustomerGroup  $channel
     * @return void
     */
    public function updated(CustomerGroup $customerGroup)
    {
        if ($customerGroup->default) {
            $this->makeOtherRecordsNonDefault($customerGroup);
        }
    }

    /**
     * Handle the Channel "created" event.
     *
     * @param  \GetCandy\Api\Core\Customers\Models\CustomerGroup  $channel
     * @return void
     */
    public function created(CustomerGroup $customerGroup)
    {
        if ($customerGroup->default) {
            $this->makeOtherRecordsNonDefault($customerGroup);
        }
    }

    /**
     * Sets records apart from the one passed to not be default.
     *
     * @param   CustomerGroup  $customerGroup
     *
     * @return  void
     */
    protected function makeOtherRecordsNonDefault(CustomerGroup $customerGroup)
    {
        CustomerGroup::whereDefault(true)->where('id', '!=', $customerGroup->id)->update([
            'default' => false,
        ]);
    }
}
