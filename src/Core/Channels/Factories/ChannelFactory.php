<?php

namespace GetCandy\Api\Core\Channels\Factories;

use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;
use GetCandy\Api\Core\Channels\Services\ChannelService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ChannelFactory implements ChannelFactoryInterface
{
    /**
     * @var \GetCandy\Api\Core\Channels\Models\Channel
     */
    protected $channel;

    /**
     * @var \GetCandy\Api\Core\Channels\Services\ChannelService
     */
    protected $service;

    public function __construct(ChannelService $channels)
    {
        $this->service = $channels;
    }

    /**
     * Set the value for channel.
     *
     * @param  null|string|\GetCandy\Api\Core\Channels\Models\Channel  $channel
     * @return void
     */
    public function set($channel = null)
    {
        if (! $channel) {
            $channel = $this->service->getDefaultRecord();
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
                $channel = $this->service->getByHandle($channel);
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
