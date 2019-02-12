<?php

namespace GetCandy\Api\Core\Channels\Factories;

use GetCandy\Api\Core\Channels\Services\ChannelService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;

class ChannelFactory implements ChannelFactoryInterface
{
    /**
     * The current channel.
     *
     * @var Channel
     */
    protected $channel;

    /**
     * The channel service.
     *
     * @var ChannelService
     */
    protected $service;

    public function __construct(ChannelService $channels)
    {
        $this->service = $channels;
    }

    /**
     * Set the value for channel.
     *
     * @param string|channel $channel
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
     * @param string $channel
     * @return void
     */
    public function setChannel($channel)
    {
        if (is_string($channel)) {
            try {
                $channel = $this->service->getByHandle($channel);
            } catch (NotFoundHttpException $e) {
                $channel = $this->set();
            }
        }
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get the current channel.
     *
     * @return void
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
