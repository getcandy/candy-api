<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Drafting\Actions\DraftChannels;
use GetCandy\Api\Core\Products\Models\Product;
use Tests\TestCase;

/**
 * @group drafting
 */
class DraftChannelsTest extends TestCase
{
    public function test_can_draft_model_channels()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $product->id,
        ]);

        factory(Channel::class, 2)->create()->each(function ($channel) use ($product) {
            $product->channels()->attach($channel->id, [
                'published_at' => now(),
            ]);
        });

        $this->assertCount(2, $product->channels);
        $this->assertCount(0, $draft->channels);

        (new DraftChannels())->actingAs($user)->run([
            'parent' => $product,
            'draft' => $draft,
        ]);

        $this->assertCount(2, $draft->refresh()->channels);
    }
}
