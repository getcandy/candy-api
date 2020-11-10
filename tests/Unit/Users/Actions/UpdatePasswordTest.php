<?php

namespace Tests\Unit\Users\Actions;

use GetCandy\Api\Core\Users\Actions\UpdatePassword;
use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group channels
 */
class UpdatePasswordTest extends FeatureCase
{
    public function test_can_change_user_password()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $attributes = [
            'current_password' => 'secret',
            'new_password' => 'supersecret',
            'user' => $user,
        ];

        $result = UpdatePassword::run($attributes);

        $this->assertNotFalse($result);

        $this->assertNotEquals(bcrypt($attributes['current_password']), $result->password);
    }

    public function test_password_length_fails_if_too_short()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $attributes = [
            'current_password' => 'secret',
            'new_password' => 'test',
            'user' => $user,
        ];

        $this->expectException('\Illuminate\Validation\ValidationException');

        UpdatePassword::run($attributes);
    }

    public function test_cannot_change_user_password_if_current_is_invalid()
    {
        $user = factory(User::class)->create(['password' => bcrypt('secret')]);

        $attributes = [
            'current_password' => 'secret1',
            'new_password' => 'supersecret',
            'user' => $user,
        ];

        $result = UpdatePassword::run($attributes);

        $this->assertFalse($result);
    }
}
