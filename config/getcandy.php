<?php

return [
    /**
     * List which roles have access to the hub
     */
    'hub_access' => ['editor'],

    /**
     * The URL to your storefront
     */
    'storefronturl' => env('STOREFRONT_URL'),

    /**
     * Which default customer group to use
     */
    'default_customer_group' => 'retail',

    /*
    |--------------------------------------------------------------------------
    | Discount settings
    |--------------------------------------------------------------------------
    |
    | Define what types of discount your api offers
    |
     */
    'discounters' => [
        'coupon' => GetCandy\Api\Discounts\Criteria\Coupon::class,
        'customer-groups' => GetCandy\Api\Discounts\Criteria\CustomerGroup::class,
        'products' => GetCandy\Api\Discounts\Criteria\Products::class,
        'users' => GetCandy\Api\Discounts\Criteria\Users::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment settings
    |--------------------------------------------------------------------------
    |
    | Define your payment gateways and env here
    |
     */
    'payments' => [
        'gateway' => 'braintree',
        'environment' => env('PAYMENT_ENV'),
        'providers' => [
            'offline' => GetCandy\Api\Payments\Providers\OnAccount::class,
            'braintree' => GetCandy\Api\Payments\Providers\Braintree::class,
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Search settings
    |--------------------------------------------------------------------------
    |
    | This is where you define all your search settings.
    |
    | Client: This is the search client
    | index_prefix: What your search index should be prefixed with
    | index: What index to search on by default
    |
    */
    'search' => [
        'client' => \GetCandy\Api\Search\Elastic\Elastic::class,
        'index_prefix' => env('SEARCH_INDEX_PREFIX', 'candy'),
        'index' => env('SEARCH_INDEX', 'candy_products_en')
    ]
];
