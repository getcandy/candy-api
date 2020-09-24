<?php

namespace Tests\Unit\Channels\Actions;

use GetCandy\Api\Core\Channels\Actions\SetCurrentChannel;
use GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface;
use GetCandy\Api\Core\Channels\Models\Channel;
use Tests\Feature\FeatureCase;

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
            'handle' => $channel->handle,
        ]);

        $this->assertEquals($channel->id, $factory->getChannel()->id);
    }
}
