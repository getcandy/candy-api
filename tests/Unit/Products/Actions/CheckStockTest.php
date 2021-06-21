<?php

namespace Tests\Unit\Products\Actions\Versioning;

use Tests\TestCase;
use GetCandy\Api\Core\Orders\Models\Order;
use GetCandy\Api\Core\Orders\Models\OrderLine;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Products\Actions\CheckStock;
use GetCandy\Api\Core\Products\Models\ProductVariant;

/**
 * @group stock_one
 */
class CheckStockTest extends TestCase
{
    public function test_can_check_stock_by_variant_id()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
            'stock' => 15,
        ]);

        $check = CheckStock::run([
            'variant_id' => $variant->encoded_id,
            'quantity' => 1,
        ]);
        $this->assertTrue($check);
    }

    public function test_stock_check_fails()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
            'stock' => 1,
            'backorder' => 'in-stock',
        ]);

        $check = CheckStock::run([
            'variant_id' => $variant->encoded_id,
            'quantity' => 12,
        ]);

        $this->assertFalse($check);
    }

    public function test_backorder_products_pass_stock_check()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
            'stock' => 1,
            'backorder' => 'always',
        ]);

        $check = CheckStock::run([
            'variant_id' => $variant->encoded_id,
            'quantity' => 12,
        ]);

        $this->assertTrue($check);
    }

    public function test_expected_products_have_stock_checked()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
            'stock' => 1,
            'incoming' => 12,
            'backorder' => 'expected',
        ]);

        $this->assertTrue(
            CheckStock::run([
                'variant_id' => $variant->encoded_id,
                'quantity' => 12,
            ])
        );

        $this->assertFalse(
            CheckStock::run([
                'variant_id' => $variant->encoded_id,
                'quantity' => 15,
            ])
        );
    }

    public function test_orders_reserved_stock_is_taken_into_account()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
            'backorder' => 'in-stock',
            'stock' => 5,
        ]);

        $order = factory(Order::class)->create([
            'expires_at' => now()->addMinutes(60),
        ]);
        factory(OrderLine::class)->create([
            'order_id' => $order->id,
            'quantity' => 3,
            'sku' => $variant->sku,
        ]);

        $check = CheckStock::run([
            'variant_id' => $variant->encoded_id,
            'quantity' => 5,
            'order_id' => $order->encoded_id,
        ]);

        $this->assertTrue($check);
    }

    public function test_reserved_stock_is_accounted_for_on_guest_order()
    {
        $user = $this->admin();

        $product = factory(Product::class)->create();

        $variant = factory(ProductVariant::class)->create([
            'product_id' => $product->id,
            'backorder' => 'in-stock',
            'stock' => 5,
        ]);

        $order = factory(Order::class)->create([
            'expires_at' => now()->addMinutes(60),
        ]);
        factory(OrderLine::class)->create([
            'order_id' => $order->id,
            'quantity' => 3,
            'sku' => $variant->sku,
        ]);

        $check = CheckStock::run([
            'variant_id' => $variant->encoded_id,
            'quantity' => 5,
        ]);

        $this->assertFalse($check);
    }
}
