<?php

namespace Tests\Unit\Users\Actions;

use GetCandy\Api\Core\Auth\Actions\FetchImpersonationToken;
use Tests\Feature\FeatureCase;
use Tests\Stubs\User;

/**
 * @group channels
 */
class FetchImpersonationTokenTest extends FeatureCase
{
    public function test_can_fetch_a_impersonation_token()
    {
        $admin = $this->admin();

        $user = factory(User::class)->create();

        $result = (new FetchImpersonationToken)->actingAs($admin)->run([
            'encoded_id' => $user->encoded_id,
        ]);

        $this->assertNotEmpty($result);
    }
}
