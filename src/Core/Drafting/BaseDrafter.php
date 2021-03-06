<?php

namespace GetCandy\Api\Core\Drafting;

use Illuminate\Support\Facades\Log;

abstract class BaseDrafter
{
    protected $extendedDraftActions = [];
    protected $extendedPublishActions = [];

    public function addDraftAction($action)
    {
        return $this->addAction('extendedDraftActions', $action);
    }

    public function addPublishAction($action)
    {
        return $this->addAction('extendedPublishActions', $action);
    }

    protected function addAction($target, $incoming)
    {
        if (is_array($incoming)) {
            $this->{$target} = array_merge($this->{$target}, $incoming);

            return;
        }
        array_push($this->{$target}, $incoming);
    }

    protected function callActions(array $actions, array $params = [])
    {
        foreach ($actions as $action) {
            if (! class_exists($action)) {
                Log::error("Tried to call action ${action} but it doesn't exist");
                continue;
            }
            call_user_func("{$action}::run", $params);
        }
    }

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
