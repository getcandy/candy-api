<?php

namespace Tests\Unit\Versioning\Actions;

use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Versioning\Actions\CreateVersion;
use Tests\TestCase;

/**
 * @group versioning
 */
class CreateVersionTest extends TestCase
{
    public function test_can_create_a_version_of_a_model()
    {
        $user = $this->admin();

        $model = factory(Product::class)->create();

        $version = (new CreateVersion())->actingAs($user)->run([
            'model' => $model,
        ]);

        $this->assertEquals($model->id, $version->versionable_id);
        $this->assertEquals(get_class($model), $version->versionable_type);

        $versionModelData = $version->model_data;

        foreach ($versionModelData as $attribute => $value) {
            $this->assertEquals($model->{$attribute}, $value);
        }
    }
}
