<?php

namespace GetCandy\Api\Core\Drafting;

abstract class BaseDrafter
{
    /**
     * Process the assets for a duplicated product.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $old
     * @param  \Illuminate\Database\Eloquent\Model  $new
     * @return void
     */
    protected function processAssets($old, &$new)
    {
        foreach ($old->assets as $asset) {
            $new->assets()->attach(
                $asset->id,
                [
                    'primary' => $asset->pivot->primary,
                    'assetable_type' => $asset->pivot->assetable_type,
                ]
            );
        }
    }

    /**
     * Process the customer groups for the duplicated product.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $old
     * @param  \Illuminate\Database\Eloquent\Model  $new
     * @return void
     */
    protected function processCustomerGroups($old, &$new)
    {
        // Need to associate all the channels the current product has
        // but make sure they are not active to start with.
        $groups = $old->customerGroups;

        $newGroups = collect();

        foreach ($groups as $group) {
            // \DB::table()
            $newGroups->put($group->id, [
                'visible' => $group->pivot->visible,
                'purchasable' => $group->pivot->purchasable,
            ]);
        }

        $new->customerGroups()->sync($newGroups->toArray());
    }

    /**
     * Process channels for a duplicated product.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $old
     * @param  \Illuminate\Database\Eloquent\Model  $new
     * @return void
     */
    protected function processChannels($old, &$new)
    {
        // Need to associate all the channels the current product has
        // but make sure they are not active to start with.
        $channels = $old->channels;

        $newChannels = collect();

        foreach ($channels as $channel) {
            $newChannels->put($channel->id, [
                'published_at' => now(),
            ]);
        }

        $new->channels()->sync($newChannels->toArray());
    }
}
