<?php

namespace Tests\Unit\Attributes\Actions;

use DB;
use Tests\TestCase;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Attributes\Actions\FetchAttributables;

/**
 * @group attributes
 */
class FetchAttributablesTest extends TestCase
{
    public function test_can_fetch_attributables()
    {
        $user = $this->admin();

        DB::table('attributables')->insert([
            [
                'attribute_id' => 1,
                'attributable_id' => 1,
                'attributable_type' => Product::class,
            ],
            [
                'attribute_id' => 2,
                'attributable_id' => 2,
                'attributable_type' => Product::class,
            ]
        ]);

        $attributables = DB::table('attributables')->get();


        $productIds = [];

        foreach ($attributables as $att) {
            $productIds[] = (new Product)->encode($att->attributable_id);
        }

        $ids = array_unique($productIds);

        $result = (new FetchAttributables)->actingAs($user)->run([
            'encoded_ids' => $ids,
            'type' => Product::class
        ]);

        $this->assertCount(count($ids), $result);
    }
}
