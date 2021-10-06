<?php

namespace Tests\Unit\Versioning\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use GetCandy\Api\Core\Versioning\Actions\VersionChannels;
use Tests\TestCase;

/**
 * @group versioning
 */
class VersionChannelsTest extends TestCase
{
    public function test_can_create_a_version_of_a_model()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        factory(Channel::class, 2)->create()->each(function ($channel) use ($product) {
            $product->channels()->attach($channel->id, [
                'published_at' => now(),
            ]);
        });

        $this->assertCount(2, $product->channels);

        $version = (new CreateVersion())->actingAs($user)->run([
            'model' => $product,
        ]);

        (new VersionChannels())->actingAs($user)->run([
            'version' => $version,
            'model' => $product,
        ]);

        $this->assertCount(2, $version->relations);

        foreach ($version->relations as $relation) {
            $this->assertEquals(Channel::class, $relation->versionable_type);
        }

        // Make sure our version has the correct channels
        foreach ($product->channels as $productChannel) {
            $versionable = $version->relations->first(function ($version) use ($productChannel) {
                return $version->versionable_id == $productChannel->id && $version->versionable_type === get_class($productChannel);
            });
            $this->assertNotNull($version);
            $this->assertEquals($productChannel->pivot->published_at, $versionable->model_data['published_at']);
        }
    }
}
