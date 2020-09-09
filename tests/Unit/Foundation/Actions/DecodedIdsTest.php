<?php

namespace Tests\Unit\Foundation\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Foundation\Actions\DecodeIds;
use Tests\TestCase;

/**
 * @group foundation
 */
class DecodeIdsTest extends TestCase
{
    public function test_can_decode_model_ids()
    {
        $models = factory(Channel::class, 5)->create();

        $ids = DecodeIds::run([
            'model' => Channel::class,
            'encoded_ids' => $models->map(function ($channel) {
                return $channel->encoded_id;
            })->toArray(),
        ]);

        $realModelIds = $models->pluck('id');

        $this->assertSame($realModelIds->toArray(), $ids);
    }
}
