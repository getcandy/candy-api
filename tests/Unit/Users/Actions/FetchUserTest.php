<?php

namespace Tests\Unit\Users\Actions;

use GetCandy\Api\Core\Users\Actions\FetchUser;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class FetchUserTest extends FeatureCase
{
    public function test_can_fetch_user_by_email()
    {
        $user = $this->admin();

        $currentUser = (new FetchUser)->actingAs($user)->run([
            'email' => $user->email,
        ]);

        $this->assertEquals($user->id, $currentUser->id);
    }
}
