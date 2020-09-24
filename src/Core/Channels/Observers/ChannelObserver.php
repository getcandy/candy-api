<?php

namespace GetCandy\Api\Core\Channels\Observers;

use GetCandy\Api\Core\Channels\Models\Channel;

class ChannelObserver
{
    /**
     * Handle the Channel "updated" event.
     *
     * @param  \GetCandy\Api\Core\Channels\Models\Channel  $channel
     * @return void
     */
    public function updated(Channel $channel)
    {
        if ($channel->default) {
            Channel::whereDefault(true)->where('id', '!=', $channel->id)->update([
                'default' => false,
            ]);
        }
    }
}
