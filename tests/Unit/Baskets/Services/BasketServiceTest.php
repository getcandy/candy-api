<?php

namespace Tests\Unit\Orders\Services;

use Carbon\Carbon;
use Tests\TestCase;
use Tests\Stubs\User;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Orders\Services\OrderService;
use GetCandy\Api\Core\Baskets\Services\BasketService;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;

/**
 * @group baskets
 */
class BasketServiceTest extends TestCase
{
    public function test_can_fetch_existing_basket()
    {
        $service = $this->app->make(BasketService::class);

        $basket = $this->getinitalbasket();

        $existing = $service->getByHashedId($basket->encodedId());

        $this->assertEquals($basket->id, $existing->id);
    }

    public function test_cant_get_processed_basket()
    {
        $service = $this->app->make(BasketService::class);

        $user = User::first();
        $basket = $this->getinitalbasket($user);

        $this->assertEquals($basket->user->id, $user->id);

        $order = $this->app->make(OrderService::class)->store($basket->encodedId());
        $order->update(['placed_at' => Carbon::now()]);

        $newBasket = $service->getCurrentForUser($user);

        $this->assertNotEquals($basket->id, $newBasket->id);
    }

    public function test_can_create_guest_baskets()
    {
        $service = $this->app->make(BasketService::class);

        $variant = ProductVariant::first();
        $variant = $this->app->make(ProductVariantFactory::class)->init($variant)->get();

        $payload = [
            'variants' => [
                [
                    'id' => $variant->encodedId(),
                    'quantity' => 1,
                ],
            ],
        ];
        $basket = $service->store($payload);

        $this->assertInstanceOf(Basket::class, $basket);

        $this->assertCount(1, $basket->lines);
        $this->assertEquals($basket->sub_total, $variant->unit_cost);
        $this->assertEquals($basket->total_tax, $variant->unit_tax);

        foreach ($basket->lines as $line) {
            $this->assertEquals($line->variant->unit_cost, $line->total);
        }
    }

    public function test_can_set_meta_on()
    {
        $service = $this->app->make(BasketService::class);
        $variant = ProductVariant::first();
        $variant = $this->app->make(ProductVariantFactory::class)->init($variant)->get();

        $payload = [
            'meta' => [
                'big_basket' => true,
            ],
            'variants' => [
                [
                    'id' => $variant->encodedId(),
                    'quantity' => 1,
                    'meta' => [
                        'backorder' => 5,
                    ],
                ],
            ],
        ];

        $basket = $service->store($payload);
        // Get the basket line
        $line = $basket->lines->first(function ($line) use ($variant) {
            return $line->variant->sku === $variant->sku;
        });

        $this->assertSame(['backorder' => 5], $line->meta);
        $this->assertSame(['big_basket' => true], $basket->meta);
    }
}
