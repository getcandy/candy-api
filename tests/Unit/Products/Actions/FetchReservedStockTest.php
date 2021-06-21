<?php

namespace Tests\Unit\Products\Actions\Versioning;

use Tests\TestCase;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Orders\Models\OrderLine;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Products\Actions\FetchReservedStock;

/**
 * @group stock
 */
class FetchReservedStockTest extends TestCase
{
    public function test_product_has_no_reserved_stock()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
        ]);

        $reservedStock = FetchReservedStock::run([
            'sku' => $variant->sku,
        ]);

        $this->assertEquals(0, $reservedStock);
    }

    public function test_product_has_reserved_stock()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
        ]);

        $order = factory(Order::class)->create([
            'expires_at' => now()->addMinutes(60),
        ]);

        factory(OrderLine::class)->create([
            'order_id' => $order->id,
            'sku' => $variant->sku,
        ]);

        $reservedStock = FetchReservedStock::run([
            'sku' => $variant->sku,
        ]);

        $this->assertEquals(1, $reservedStock);
    }

    public function test_product_reservation_doesnt_happen_on_expired_orders()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
        ]);

        $order = factory(Order::class)->create([
            'expires_at' => now()->subMinutes(60),
        ]);

        factory(OrderLine::class)->create([
            'order_id' => $order->id,
            'sku' => $variant->sku,
        ]);

        $reservedStock = FetchReservedStock::run([
            'sku' => $variant->sku,
        ]);

        $this->assertEquals(0, $reservedStock);
    }

    public function test_product_is_reserved_across_multiple_orders()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
        ]);

        factory(Order::class, 5)->create([
            'expires_at' => now()->addMinutes(60),
        ])->each(function ($order) use ($variant) {
            factory(OrderLine::class)->create([
                'order_id' => $order->id,
                'sku' => $variant->sku,
            ]);
        });

        $reservedStock = FetchReservedStock::run([
            'sku' => $variant->sku,
        ]);

        $this->assertEquals(5, $reservedStock);
    }
}
