<?php

namespace Tests\Unit\Channels\Actions;

use GetCandy\Api\Core\Channels\Actions\DeleteChannel;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Exceptions\DefaultRecordRequiredException;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class DeleteChannelTest extends FeatureCase
{
    public function test_exception_thrown_when_deleting_default_record()
    {
        $user = $this->admin();
        $channel = factory(Channel::class)->create();

        $channel->default = true;
        $channel->save();

        $this->expectException(DefaultRecordRequiredException::class);

        (new DeleteChannel)->actingAs($user)->run([
            'encoded_id' => $channel->encoded_id,
        ]);
    }
}
