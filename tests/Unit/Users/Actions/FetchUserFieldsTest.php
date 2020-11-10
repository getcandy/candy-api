<?php

namespace Tests\Unit\Users\Actions;

use GetCandy\Api\Core\Users\Actions\FetchUserFields;
use Tests\Feature\FeatureCase;

/**
 * @group channels
 */
class FetchUserFieldsTest extends FeatureCase
{
    public function test_can_fetch_user_fields()
    {
        $fields = (new FetchUserFields)->run();

        $this->assertContains($fields, [
            [],
            config('getcandy.user.fields'),
        ]);
    }
}
