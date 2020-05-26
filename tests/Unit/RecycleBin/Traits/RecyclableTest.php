<?php

namespace Tests\Unit\RecycleBin\Traits;

use DB;
use GetCandy\Api\Core\Products\Models\Product;
use Tests\TestCase;

/**
 * @group recycling
 */
class RecyclableTest extends TestCase
{
    public function test_model_is_automatically_added_and_removed_from_bin()
    {
        $product = factory(Product::class)->create();

        $this->assertDatabaseMissing('recycle_bin', [
            'recyclable_id' => $product->id,
            'recyclable_type' => Product::class,
        ]);

        $product->delete();

        $this->assertNotNull($product->deleted_at);

        $this->assertDatabaseHas('recycle_bin', [
            'recyclable_id' => $product->id,
            'recyclable_type' => Product::class,
        ]);

        $product->restore();

        $this->assertDatabaseMissing('recycle_bin', [
            'recyclable_id' => $product->id,
            'recyclable_type' => Product::class,
        ]);

        $this->assertNull($product->deleted_at);
    }

    public function test_model_only_gets_add_to_bin_once()
    {
        $product = factory(Product::class)->create();

        $product->delete();

        $rows = DB::table('recycle_bin')->where([
            'recyclable_id' => $product->id,
            'recyclable_type' => Product::class,
        ])->get();

        $this->assertCount(1, $rows);
    }

    public function test_model_gets_removed_from_bin_when_hard_deleted()
    {
        $product = factory(Product::class)->create();

        $product->delete();

        $rows = DB::table('recycle_bin')->where([
            'recyclable_id' => $product->id,
            'recyclable_type' => Product::class,
        ])->get();

        $this->assertCount(1, $rows);

        $product->forceDelete();

        $rows = DB::table('recycle_bin')->where([
            'recyclable_id' => $product->id,
            'recyclable_type' => Product::class,
        ])->get();

        $this->assertCount(0, $rows);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }
}
