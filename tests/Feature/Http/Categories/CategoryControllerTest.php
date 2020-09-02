<?php

namespace Tests\Feature\Http\Controllers\Attributes;

use GetCandy\Api\Core\Categories\Models\Category;
use Tests\Feature\FeatureCase;

/**
 * @group categories
 */
class CategoryControllerTest extends FeatureCase
{
    /**
     * @group fail
     *
     * @return  [type]  [return description]
     */
    public function test_can_list_all_categories()
    {
        Category::create([
            'attribute_data' => [
                'webstore' => [
                    'en' => 'Test category',
                ],
            ],
        ]);

        $user = $this->admin();
        $response = $this->actingAs($user)->json('GET', 'categories');

        $response->assertStatus(200);
        $this->assertResponseValid($response, '/categories');
    }

    public function test_can_show_a_category_by_id()
    {
        $user = $this->admin();
        Category::create([
            'attribute_data' => [
                'webstore' => [
                    'en' => 'Test category',
                ],
            ],
        ]);
        $categoryId = Category::withoutGlobalScopes()->first()->encodedId();
        $response = $this->actingAs($user)->json('GET', "categories/{$categoryId}");
        $response->assertStatus(200);

        $this->assertResponseValid($response, '/categories/{categoryId}');
    }

    public function test_missing_shows_appropriate_response()
    {
        $user = $this->admin();
        $response = $this->actingAs($user)->json('GET', 'categories/9999');
        $response->assertStatus(404);

        // $this->assertResponseValid($response, '/categories/{categoryId}');
    }

    public function test_can_update_a_category()
    {
        $user = $this->admin();
        Category::create([
            'attribute_data' => [
                'webstore' => [
                    'en' => 'Test category',
                ],
            ],
        ]);
        $category = Category::withoutGlobalScopes()->first();
        $categoryId = $category->encodedId();
        $response = $this->actingAs($user)->json('PUT', "categories/{$categoryId}", [
            'attribute_data' => [
                'name' => [
                    'webstore' => [
                        'en' => 'Updated test category',
                    ],
                ],
            ],
        ]);
        $response->assertStatus(200);

        $categoryName = $category->refresh()->attribute('name', 'webstore', 'en');
        $this->assertEquals('Updated test category', $categoryName);

        $this->assertResponseValid($response, '/categories/{categoryId}', 'put');
    }

    public function test_validation_works_on_update()
    {
        $user = $this->admin();
        Category::create([
            'attribute_data' => [
                'webstore' => [
                    'en' => 'Test category',
                ],
            ],
        ]);
        $category = Category::withoutGlobalScopes()->first();
        $categoryId = $category->encodedId();
        $response = $this->actingAs($user)->json('PUT', "categories/{$categoryId}");
        $response->assertStatus(422);
        $this->assertResponseValid($response, '/categories/{categoryId}', 'put');
    }
}
