<?php

namespace GetCandy\Api\Core\Channels\Factories;

use GetCandy\Api\Core\Channels\Actions\FetchChannel;
use GetCandy\Api\Core\Channels\Actions\FetchDefaultChannel;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ChannelFactory implements ChannelFactoryInterface
{
    /**
     * @var \GetCandy\Api\Core\Channels\Models\Channel
     */
    protected $channel;

    /**
     * Set the value for channel.
     *
     * @param  null|string|\GetCandy\Api\Core\Channels\Models\Channel  $channel
     * @return void
     */
    public function set($channel = null)
    {
        if (! $channel) {
            $channel = FetchDefaultChannel::run();
        }
        $this->setChannel($channel);
    }

    /**
     * Set the value for channel.
     *
     * @param  string|\GetCandy\Api\Core\Channels\Models\Channel  $channel
     * @return $this
     */
    public function setChannel($channel)
    {
        if (is_string($channel)) {
            try {
                $channel = FetchChannel::run([
                    'handle' => $channel,
                ]);
            } catch (ModelNotFoundException $e) {
                $channel = $this->set();
            }
        }
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get the current channel.
     *
     * @return \GetCandy\Api\Core\Channels\Models\Channel
     */
    public function getChannel()
    {
        if (! $this->channel) {
            $this->set();
        }

        return $this->channel;
    }

    public function current()
    {
        return $this->channel->handle ?? null;
    }
}
