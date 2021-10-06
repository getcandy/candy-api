<?php

namespace Tests\Unit\Drafting\Actions;

use GetCandy\Api\Core\Assets\Models\Asset;
use GetCandy\Api\Core\Assets\Models\AssetSource;
use GetCandy\Api\Core\Drafting\Actions\DraftAssets;
use GetCandy\Api\Core\Products\Models\Product;
use Tests\TestCase;

/**
 * @group drafting
 */
class DraftAssetsTest extends TestCase
{
    public function test_can_draft_model_assets()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();
        $draft = factory(Product::class)->create();
        $draft->update([
            'drafted_at' => now(),
            'draft_parent_id' => $product->id,
        ]);

        factory(AssetSource::class)->create()->each(function ($source) use ($product) {
            $source->assets()->createMany(
                factory(Asset::class, 2)->make()->toArray()
            );

            foreach ($source->assets as $asset) {
                $product->assets()->attach($asset->id, [
                    'primary' => 1,
                    'position' => 1,
                    'assetable_type' => Product::class,
                ]);
            }
        });

        $this->assertCount(2, $product->assets);
        $this->assertCount(0, $draft->assets);

        (new DraftAssets())->actingAs($user)->run([
            'parent' => $product,
            'draft' => $draft,
        ]);

        $this->assertCount(2, $draft->refresh()->assets);
    }
}
