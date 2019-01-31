<?php

namespace Seeds;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Layouts\Models\Layout;
use GetCandy\Api\Core\Channels\Models\Channel;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;

class ProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $language = Language::first()->id;
        $tax = app('api')->taxes()->getDefaultRecord();

        $basic = Layout::create([
            'name' => 'Basic product',
            'handle' => 'basic-product',
        ])->id;

        $featured = Layout::create([
            'name' => 'Featured product',
            'handle' => 'featured-product',
        ])->id;

        $data = [
            'layout' => $basic,
            'option_data' => [
                'size' => [
                    'position' => 2,
                    'label' => [
                        'en' => 'Size',
                        'sv' => 'Storlek',
                    ],
                    'options' => [
                        '12' => [
                            'position' => 2,
                            'values' => [
                                'en' => '12',
                                'sv' => '13',
                            ],
                        ],
                        '10' => [
                            'position' => 1,
                            'values' => [
                                'en' => '10',
                                'sv' => '11',
                            ],
                        ],
                    ],
                ],
                'colour' => [
                    'position' => 1,
                    'label' => [
                        'en' => 'Colour',
                        'sv' => 'Färg',
                    ],
                    'options' => [
                        'black' => [
                            'position' => 1,
                            'values' => [
                                'en' => 'Black',
                                'sv' => 'Svart',
                            ],
                        ],
                        'brown' => [
                            'position' => 2,
                            'values' => [
                                'en' => 'Brown',
                                'sv' => 'Brun',
                            ],
                        ],

                    ],
                ],
            ],
            'variants' => [
                [
                    'sku' => '123-12',
                    'price' => 10.00,
                    'stock' => 1,
                    'options' => [
                        'size' => '12',
                        'colour' => 'brown',
                    ],
                ],
                [
                    'sku' => '123-10',
                    'price' => 10.00,
                    'stock' => 1,
                    'options' => [
                        'size' => '10',
                        'colour' => 'brown',
                    ],
                ],
                [
                    'sku' => '456-12',
                    'price' => 10.00,
                    'stock' => 1,
                    'options' => [
                        'size' => '12',
                        'colour' => 'black',
                    ],
                ],
                [
                    'sku' => '456-10',
                    'price' => 10.00,
                    'stock' => 1,
                    'options' => [
                        'size' => '10',
                        'colour' => 'black',
                    ],
                ],
            ],
            'attribute_data' => [
                'name' => [
                    'en' => 'Red Dog',
                    'sv' => 'Röd hund',
                ],
                'material' => [
                    'en' => 'Leather',
                    'sv' => 'Läder',
                ],
                'description' => [
                    'en' => 'Legendary lightweight boots made by Blundstone in Tasmania since 1932. Their iconic soles have been engineered for optimum comfort, shock absorption and all-weathers.',
                    'sv' => 'Legendariska lätta stövlar gjorda av Blundstone i Tasmanien sedan 1932. Deras ikoniska sålar har konstruerats för optimal komfort, stötdämpning och alla väder.',
                ],
            ],
        ];

        $attributes = Attribute::get();

        $fake = \Faker\Factory::create();

        $family = ProductFamily::find(1);

        $product = Product::create([
            'attribute_data' => $data['attribute_data'],
            'option_data' => (! empty($data['option_data']) ? $data['option_data'] : []),
        ]);

        $groups = CustomerGroup::all();
        $channel = Channel::find(1);

        foreach ($groups as $group) {
            $product->customerGroups()->attach($group->id, [
                'visible' => 1,
                'purchasable' => 1,
            ]);
        }

        $product->channels()->attach($channel->id, [
            'published_at' => Carbon::now(),
        ]);

        foreach ($attributes as $att) {
            $product->attributes()->attach($att);
        }

        $product->layout()->associate($data['layout']);
        $product->family()->associate($family);
        foreach ($product->attribute_data['name'] as $channel => $attr_data) {
            if ($channel == 'ecommerce') {
                $product->route()->create([
                    'default' => true,
                    'slug' => str_slug($attr_data['en']),
                    'locale' => 'en',
                ]);
            }
        }

        $product->save();

        if (! empty($data['variants'])) {
            foreach ($data['variants'] as $variant) {
                $product->variants()->create($variant);
            }
        } else {
            $product->variants()->create([
                'options' => [],
                'sku' => str_random(8),
                'stock' => 1,
                'price' => 40,
            ]);
        }

        foreach ($product->variants as $variant) {
            $variant->tax_id = $tax->id;
            $variant->save();
        }

        $data = [
            'layout' => $basic,
            'option_data' => [
                'size' => [
                    'position' => 2,
                    'label' => [
                        'en' => 'Size',
                        'sv' => 'Storlek',
                    ],
                    'options' => [
                        '12' => [
                            'position' => 2,
                            'values' => [
                                'en' => '12',
                                'sv' => '13',
                            ],
                        ],
                        '10' => [
                            'position' => 1,
                            'values' => [
                                'en' => '10',
                                'sv' => '11',
                            ],
                        ],
                    ],
                ],
                'colour' => [
                    'position' => 1,
                    'label' => [
                        'en' => 'Colour',
                        'sv' => 'Färg',
                    ],
                    'options' => [
                        'black' => [
                            'position' => 1,
                            'values' => [
                                'en' => 'Black',
                                'sv' => 'Svart',
                            ],
                        ],
                        'brown' => [
                            'position' => 2,
                            'values' => [
                                'en' => 'Brown',
                                'sv' => 'Brun',
                            ],
                        ],

                    ],
                ],
            ],
            'variants' => [
                [
                    'sku' => '335522',
                    'price' => 3000,
                    'stock' => 1,
                    'options' => [
                        'size' => '12',
                        'colour' => 'brown',
                    ],
                ],
                [
                    'sku' => '102020',
                    'price' => 2500,
                    'stock' => 1,
                    'options' => [
                        'size' => '10',
                        'colour' => 'brown',
                    ],
                ],
            ],
            'attribute_data' => [
                'name' => [
                    'en' => 'Leather Boots',
                    'sv' => 'Läder Buts',
                ],
                'material' => [
                    'en' => 'Leather',
                    'sv' => 'Läder',
                ],
                'description' => [
                    'en' => 'Legendary lightweight boots made by Blundstone in Tasmania since 1932. Their iconic soles have been engineered for optimum comfort, shock absorption and all-weathers.',
                    'sv' => 'Legendariska lätta stövlar gjorda av Blundstone i Tasmanien sedan 1932. Deras ikoniska sålar har konstruerats för optimal komfort, stötdämpning och alla väder.',
                ],
            ],
        ];

        $attributes = Attribute::get();

        $fake = \Faker\Factory::create();

        $family = ProductFamily::find(1);

        $product = Product::create([
            'attribute_data' => $data['attribute_data'],
            'option_data' => (! empty($data['option_data']) ? $data['option_data'] : []),
        ]);

        $groups = CustomerGroup::all();
        $channel = Channel::find(1);

        foreach ($groups as $group) {
            $product->customerGroups()->attach($group->id, [
                'visible' => 1,
                'purchasable' => 1,
            ]);
        }

        $product->channels()->attach($channel->id, [
            'published_at' => Carbon::now(),
        ]);

        foreach ($attributes as $att) {
            $product->attributes()->attach($att);
        }

        $product->layout()->associate($data['layout']);
        $product->family()->associate($family);
        foreach ($product->attribute_data['name'] as $channel => $attr_data) {
            if ($channel == 'ecommerce') {
                $product->route()->create([
                    'default' => true,
                    'slug' => str_slug($attr_data['en']),
                    'locale' => 'en',
                ]);
            }
        }

        $product->save();

        if (! empty($data['variants'])) {
            foreach ($data['variants'] as $variant) {
                $product->variants()->create($variant);
            }
        } else {
            $product->variants()->create([
                'options' => [],
                'sku' => 'LTHRBTS',
                'stock' => 1,
                'price' => 2500,
            ]);
        }

        foreach ($product->variants as $variant) {
            $variant->tax_id = $tax->id;
            $variant->save();
        }
    }
}
