<?php

namespace Tests\Unit\Users\Actions;

use GetCandy\Api\Core\Users\Actions\FetchCurrentUser;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class FetchCurrentUserTest extends FeatureCase
{
    public function test_can_fetch_current_user()
    {
        $user = $this->admin();

        $currentUser = (new FetchCurrentUser)->actingAs($user)->run();

        $this->assertEquals($user->id, $currentUser->id);
    }
}
