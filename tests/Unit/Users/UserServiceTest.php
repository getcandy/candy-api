<?php

namespace Tests\Unit\Shipping\Factories;

use GetCandy\Api\Core\Users\Services\UserService;
use Tests\Stubs\User;
use Tests\TestCase;

/**
 * @group users
 */
class UserServiceTest extends TestCase
{
    public function test_can_instantiate_user_model()
    {
        $service = app()->getInstance()->make(UserService::class);
        $this->assertSame(get_class($service->model), User::class);
    }
}
