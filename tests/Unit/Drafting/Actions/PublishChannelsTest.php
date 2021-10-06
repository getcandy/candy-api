<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Drafting\Actions\PublishChannels;
use GetCandy\Api\Core\Products\Models\Product;
use Tests\TestCase;

/**
 * @group drafting
 */
class PublishChannelsTest extends TestCase
{
    public function test_can_publish_model_channels()
    {
        $user = $this->admin();

        $parent = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $parent->id,
        ]);

        factory(Channel::class, 2)->create()->each(function ($channel) use ($draft) {
            $draft->channels()->attach($channel->id, [
                'published_at' => now(),
            ]);
        });

        $this->assertCount(2, $draft->channels);
        $this->assertCount(0, $parent->channels);

        (new PublishChannels())->actingAs($user)->run([
            'parent' => $parent,
            'draft' => $draft,
        ]);

        $this->assertCount(2, $parent->refresh()->channels);
    }
}
