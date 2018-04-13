<?php

namespace Tests;

use Auth;
use GetCandy\Api\Baskets\Models\Basket;
use GetCandy\Api\Products\Models\ProductVariant;

/**
 * @group services
 */
class BasketServiceTest extends TestCase
{
    public function testResolveWithoutMerge()
    {
        // Create a basket as a guest
        $guestBasket = $this->createGuestBasket();

        dump('Guest: '.$guestBasket->id);

        $user = \Auth::loginUsingId(1);

        $userBasket = $this->createUserBasket();

        $basket = app('api')->baskets()->resolve($user, $guestBasket->encodedId());

        // TODO: Figure out why this is being a douchebag and not showing the association with the basket to the user
        // dd($user->basket);
        // echo '---';
        // dump($user->basket->id, $guestBasket->id);

        // $this->assertTrue($user->basket->encodedId() == $guestBasket->encodedId());
    }

    protected function createGuestBasket()
    {
        $lines = ProductVariant::take(2)->get()->map(function ($item) {
            return [
                'id' => $item->encodedId(),
                'price' => $item->price,
                'quantity' => 1,
            ];
        });
        $basket = app('api')->baskets()->create([
            'variants' => $lines->toArray(),
        ]);
        $this->assertTrue($basket instanceof Basket);
        $this->assertNull($basket->user);

        return $basket;
    }

    protected function createUserBasket()
    {
        $lines = ProductVariant::take(2)->get()->map(function ($item) {
            return [
                'id' => $item->encodedId(),
                'price' => $item->price,
                'quantity' => 2,
            ];
        });
        $basket = app('api')->baskets()->create([
            'variants' => $lines->toArray(),
        ], Auth::user());
        $this->assertTrue($basket instanceof Basket);

        return $basket;
    }
}
