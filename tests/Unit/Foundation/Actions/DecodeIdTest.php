<?php

namespace Tests\Unit\Foundation\Actions;

use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Customers\Models\Customer;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Foundation\Actions\DecodeId;
use GetCandy\Api\Core\Products\Models\Product;
use Tests\TestCase;

/**
 * @group foundation
 */
class DecodeIdTest extends TestCase
{
    public function test_can_decode_model_ids()
    {
        $models = [
            factory(Channel::class)->create(),
            factory(CustomerGroup::class)->create(),
            factory(Customer::class)->create(),
            factory(Product::class)->create(),
        ];

        foreach ($models as $model) {
            $this->assertEquals($model->id, DecodeId::run([
                'model' => get_class($model),
                'encoded_id' => $model->encoded_id,
            ]));
        }
    }
}
