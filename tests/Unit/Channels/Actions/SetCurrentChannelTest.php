<?php

namespace Tests\Unit\Channels\Actions;

use Tests\Feature\FeatureCase;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Channels\Actions\SetCurrentChannel;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;


/**
 * @group foo
 */
class SetCurrentChannelTest extends FeatureCase
{
    public function test_can_set_global_channel()
    {
        $factory = app(ChannelFactoryInterface::class);

        $channel = factory(Channel::class)->create();

        SetCurrentChannel::run([
            'handle' => $channel->handle
        ]);

        $this->assertEquals($channel->id, $factory->getChannel()->id);
    }
}