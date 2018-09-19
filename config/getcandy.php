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
    | Order settings
    |--------------------------------------------------------------------------
    |
    | This is where you define all your order settings.
    |
    */
    'orders' => [
        'mailers' => [
            // 'dispatched' => \Your\OrderDispatchedMailer::class,
            // 'payment-processing' => \Your\ConfirmationMailer::class,
        ],
        'statuses' => [

            /**
             * Setting these will help GetCandy's internal event system.
             */

            'pending' => 'payment-processing',
            'paid' => 'payment-received',
            'dispatched' => 'dispatched',

            /**
             * These are your custom order statuses, they can be whatever you want, just make
             * sure that you map the appropriate statuses above.
             */

            'options' => [
                'failed' => [
                    'label' => 'Failed',
                    'color' => '#e4002b',
                ],
                'payment-received' => [
                    'label' => 'Payment Received',
                    'color' => '#6a67ce',
                ],
                'awaiting-payment' => [
                    'label' => 'Awaiting Payment',
                    'color' => '#848a8c',
                ],
                'payment-processing' => [
                    'label' => 'Payment Processing',
                    'color' => '#b84592',
                ],
            ],
        ],
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
