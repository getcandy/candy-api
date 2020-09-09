<?php

namespace Tests\Unit\Attributes\Actions;

use Tests\TestCase;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Attributes\Actions\DeleteAttribute;

/**
 * @group attributes
 */
class DeleteAttributeTest extends TestCase
{
    public function test_can_delete_attribute_using_encoded_id()
    {
        $user = $this->admin();

        $attribute = factory(Attribute::class)->create();

        (new DeleteAttribute)->actingAs($user)->run([
            'encoded_id' => $attribute->encoded_id,
        ]);

        $this->assertDeleted('attributes', $attribute->toArray());
    }

    public function test_cannot_delete_system_attribute()
    {
        $user = $this->admin();

        $attribute = factory(Attribute::class)->create([
            'system' => true
        ]);

        (new DeleteAttribute)->actingAs($user)->run([
            'encoded_id' => $attribute->encoded_id,
        ]);

        $this->assertDatabaseHas('attributes', ['id' => $attribute->id]);
    }
}
