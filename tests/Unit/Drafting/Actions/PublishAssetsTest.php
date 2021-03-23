<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Assets\Models\AssetSource;
use GetCandy\Api\Core\Drafting\Actions\PublishAssets;
use GetCandy\Api\Core\Products\Models\Product;
use Tests\TestCase;

/**
 * @group drafting
 */
class PublishAssetsTest extends TestCase
{
    public function test_can_draft_model_assets()
    {
        $user = $this->admin();

        $parent = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $parent->id,
        ]);

        factory(AssetSource::class)->create()->each(function ($source) use ($draft) {
            $source->assets()->createMany(
                factory(Asset::class, 2)->make()->toArray()
            );

            foreach ($source->assets as $asset) {
                $draft->assets()->attach($asset->id, [
                    'primary' => 1,
                    'position' => 1,
                    'assetable_type' => Product::class,
                ]);
            }
        });

        $this->assertCount(2, $draft->assets);
        $this->assertCount(0, $parent->assets);

        (new PublishAssets)->actingAs($user)->run([
            'parent' => $parent,
            'draft' => $draft,
        ]);

        $this->assertCount(2, $parent->refresh()->assets);
    }
}
