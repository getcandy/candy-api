<?php
namespace Seeds;

use Faker\Factory;
use Illuminate\Database\Seeder;
use GetCandy\Api\Core\Layouts\Models\Layout;
use GetCandy\Api\Core\Products\Models\Product;
use GetCandy\Api\Core\Languages\Models\Language;
use GetCandy\Api\Core\Attributes\Models\Attribute;
use GetCandy\Api\Core\Products\Models\ProductFamily;
use GetCandy\Api\Core\Customers\Models\CustomerGroup;
use GetCandy\Api\Core\Products\Models\ProductVariant;

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

        $basic = Layout::create([
            'name' => 'Basic product',
            'handle' => 'basic-product'
        ])->id;

        $featured = Layout::create([
            'name' => 'Featured product',
            'handle' => 'featured-product'
        ])->id;


        $products = [
            // Boots
            'Shoes' => [
                [
                    'layout' => $basic,
                    'option_data' => [
                        'size' => [
                            'position' => 2,
                            'label' => [
                                'en' => 'Size',
                                'sv' => 'Storlek'
                            ],
                            'options' => [
                                '12' => [
                                    'position' => 2,
                                    'values' => [
                                        'en' => '12',
                                        'sv' => '13',
                                    ]
                                ],
                                '10' => [
                                    'position' => 1,
                                    'values' => [
                                        'en' => '10',
                                        'sv' => '11'
                                    ]
                                ]
                            ]
                        ],
                        'colour' => [
                            'position' => 1,
                            'label' => [
                                'en' => 'Colour',
                                'sv' => 'Färg'
                            ],
                            'options' => [
                                'black' => [
                                    'position' => 1,
                                    'values' => [
                                        'en' => 'Black',
                                        'sv' => 'Svart'
                                    ]
                                ],
                                'brown' => [
                                    'position' => 2,
                                    'values' => [
                                        'en' => 'Brown',
                                        'sv' => 'Brun'
                                    ]
                                ],

                            ]
                        ]
                    ],
                    'variants' => [
                        [
                            'sku' => '123-12',
                            'price' => 50,
                            'stock' => 1,
                            'options' => [
                                'size' => '12',
                                'colour' => 'brown'
                            ]
                        ],
                        [
                            'sku' => '123-10',
                            'price' => 50,
                            'stock' => 1,
                            'options' => [
                                'size' => '10',
                                'colour' => 'brown'
                            ]
                        ],
                        [
                            'sku' => '456-12',
                            'price' => 50,
                            'stock' => 1,
                            'options' => [
                                'size' => '12',
                                'colour' => 'black'
                            ]
                        ],
                        [
                            'sku' => '456-10',
                            'price' => 50,
                            'stock' => 1,
                            'options' => [
                                'size' => '10',
                                'colour' => 'black'
                            ]
                        ]
                    ],
                    'attribute_data' => [
                        'name' => [
                            'en' => 'Red Dog',
                            'sv' => 'Röd hund'
                        ],
                        'material' => [
                            'en' => 'Leather',
                            'sv' => 'Läder'
                        ],
                        'description' => [
                            'en' => 'Legendary lightweight boots made by Blundstone in Tasmania since 1932. Their iconic soles have been engineered for optimum comfort, shock absorption and all-weathers.',
                            'sv' => 'Legendariska lätta stövlar gjorda av Blundstone i Tasmanien sedan 1932. Deras ikoniska sålar har konstruerats för optimal komfort, stötdämpning och alla väder.'
                        ]
                    ]
                ],
                [
                    'layout' => $featured,
                    'attribute_data' => [
                        'name' => [
                            'en' => 'The Red Dog',
                            'sv' => 'Den röda hunden',
                        ],
                        'material' => [
                            'en' => 'Wool',
                            'sv' => 'Ull'
                        ],
                        'description' => [
                            'en' => 'Rainbow-striped woollen house socks. Handknitted in Nepal.  Fairtrade. Wool.',
                            'sv' => 'Rainbow-randiga ull hus strumpor. Handknitted i Nepal. Rättvis handel. Ull.'
                        ]
                    ]
                ],
                [
                    'layout' => $featured,
                    'attribute_data' => [
                        'name' => [
                            'en' => 'The Red Boat',
                            'sv' => 'Den röda hunden',
                        ],
                        'material' => [
                            'en' => 'Wool',
                            'sv' => 'Ull'
                        ],
                        'description' => [
                            'en' => 'Dog Rainbow-striped woollen house socks. Handknitted in Nepal.  Fairtrade. Wool.',
                            'sv' => 'Rainbow-randiga ull hus strumpor. Handknitted i Nepal. Rättvis handel. Ull.'
                        ]
                    ]
                ],
                [
                    'layout' => $basic,
                    'attribute_data' => [
                        'name' => [
                            'en' => 'Red Dogs',
                            'sv' => 'Röda hundar'
                        ],
                        'material' => [
                            'en' => 'Wool',
                            'sv' => 'Ull'
                        ],
                        'description' => [
                            'en' => 'Warm cable-knit house socks handknitted in Nepal from pure wool.Fairtrade',
                            'sv' => 'Varm kabelstickade husstrumpor handknitted i Nepal från ren ull.Fairtrade'
                        ]
                    ]
                ]
            ],
            'Bags' => [
                // Bags
                [
                    'layout' => $basic,
                    'attribute_data' => [
                        'name' => [
                            'en' => 'Red panda & barking dog',
                            'sv' => 'Röd panda & skällhund'
                        ],
                        'material' => [
                            'en' => 'Cotton',
                            'sv' => 'Bomull'
                        ],
                        'description' => [
                            'en' => 'A classic American tote crafted from durable 24oz cotton-canvas with steel hardware and sturdy leather handles. An adjustable long belt-like strap makes it extremely versatile. Joshu&Vela. Made in San Francisco. W55cm H30cm D21cm.',
                            'sv' => null
                        ]
                    ]
                ]
            ],
            // 'Jewellery' => [
            //     [
            //         'name' => ['gb' => 'Mesh watch', 'sv' => 'Mesh klocka'],
            //         'layout' => $basic,
            //         'attribute_data' => ['material' => ['gb' => 'Stainless Steel']]
            //     ],
            //     [
            //         'name' => ['gb' => '3 Square earrings', 'sv' => '3 kvadratiska örhängen'],
            //         'layout' => $featured,
            //         'attribute_data' => ['material' => ['gb' => 'Silver']]
            //     ],
            //     [
            //         'name' => ['gb' => 'Bird Brooch', 'sv' => 'Fågelbrosch'],
            //         'layout' => $basic,
            //         'attribute_data' => ['material' => ['gb' => 'Silver']]
            //     ]
            // ],
            // 'House items' => [
            //     [
            //         'name' => ['gb' => 'Feather dreamcatcher', 'sv' => 'Fjäderdrömskådare'],
            //         'layout' => $basic,
            //         'attribute_data' => ['material' => ['gb' => 'Leather, Feathers, Wool']]
            //     ],
            //     [
            //         'name' => ['gb' => 'Driftwood fish', 'sv' => 'Driftwood fisk'],
            //         'layout' => $featured,
            //         'attribute_data' => ['material' => ['gb' => 'Wood']]
            //     ],
            //     [
            //         'name' => ['gb' => 'Mirror Candleholder', 'sv' => 'Spegel ljushållare'],
            //         'layout' => $basic,
            //         'attribute_data' => ['material' => ['gb' => 'Glass, Metal']]
            //     ]
            // ]
        ];
        $i = 1;
        $attributes = Attribute::get();

        $fake = \Faker\Factory::create();
        foreach ($products as $family => $products) {
            $family = ProductFamily::find($i);

            foreach ($products as $data) {
                $product = Product::create([
                    'attribute_data' => $data['attribute_data'],
                    'option_data' => (!empty($data['option_data']) ? $data['option_data'] : [])
                ]);

                $group = CustomerGroup::find(1);

                $product->customerGroups()->attach($group->id, [
                    'visible' => $fake->boolean,
                    'purchasable' => $fake->boolean
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
                            'locale' => 'en'
                        ]);
                    }
                }


                $product->save();

                if (!empty($data['variants'])) {
                    foreach ($data['variants'] as $variant) {
                        $product->variants()->create($variant);
                    }
                } else {
                    $product->variants()->create([
                        'options' => [],
                        'sku' => str_random(8),
                        'stock' => 1,
                        'price' => 40
                    ]);
                }

            }
            $i++;
        }
    }
}