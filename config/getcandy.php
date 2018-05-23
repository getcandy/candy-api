<?php

return [
    /*
     * List which roles have access to the hub
     */
    'hub_access' => ['editor'],

    /*
     * The URL to your storefront
     */
    'storefronturl' => env('STOREFRONT_URL'),

    /*
     * Which default customer group to use
     */
    'default_customer_group' => 'guest',

    /*
    |--------------------------------------------------------------------------
    | Discount settings
    |--------------------------------------------------------------------------
    |
    | Define what types of discount your api offers
    |
     */
    'discounters' => [
        'coupon' => GetCandy\Api\Core\Discounts\Criteria\Coupon::class,
        'customer-groups' => GetCandy\Api\Core\Discounts\Criteria\CustomerGroup::class,
        'products' => GetCandy\Api\Core\Discounts\Criteria\Products::class,
        'users' => GetCandy\Api\Core\Discounts\Criteria\Users::class,
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
            'offline' => GetCandy\Api\Core\Payments\Providers\OnAccount::class,
            'braintree' => GetCandy\Api\Core\Payments\Providers\Braintree::class,
        ],
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
        'client' => \GetCandy\Api\Core\Search\Providers\Elastic\Elastic::class,
        'index_prefix' => env('SEARCH_INDEX_PREFIX', 'candy'),
        'index' => env('SEARCH_INDEX', 'candy_products_en'),
    ],
];
