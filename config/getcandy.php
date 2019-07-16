<?php

return [
    /*
     * List which roles have access to the hub
     */
    'hub_access' => ['editor'],

    /*
     * Define whether to use internal requests
     */
    'internal_requests' => env('CANDY_INTERNAL_REQUESTS', true),

    /*
     * The URL to your storefront
     */
    'storefronturl' => env('STOREFRONT_URL'),

    /*
     * Which default customer group to use
     */
    'default_customer_group' => 'guest',

    'token_lifetime' => 1440, // 1 day
    'refresh_token_lifetime' => 1440, // 1 day

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
        /*
         * The invoice reference prefix for an order
         * e.g {prefix}2019-04-15
         */
        'reference_prefix' => null,
        'mailers' => [
            // 'dispatched' => \Your\OrderDispatchedMailer::class,
            // 'payment-processing' => \Your\ConfirmationMailer::class,
        ],
        /*
         * These are the table columns that will appear in the hub
         */
        'table_columns' => [
            'name', 'reference', 'account_no', 'contact_email', 'type', 'account', 'order_total', 'delivery_total', 'zone', 'date',
        ],
        'statuses' => [

            /*
             * Setting these will help GetCandy's internal event system.
             */

            'pending' => 'payment-processing',
            'paid' => 'payment-received',
            'dispatched' => 'dispatched',

            /*
             * These are your custom order statuses, they can be whatever you want, just make
             * sure that you map the appropriate statuses above.
             */

            'options' => [
                'failed' => [
                    'label' => 'Failed',
                    'color' => '#e4002b',
                    'favourite' => true, // This will show as a tab in the hub
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
            'offline' => GetCandy\Api\Core\Payments\Providers\Offline::class,
            'braintree' => GetCandy\Api\Core\Payments\Providers\Braintree::class,
            'sagepay' => GetCandy\Api\Core\Payments\Providers\SagePay::class,
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
        'client_config' => [
            'elastic' => [
                'host' => null,
                'port' => null,
                'path' => null,
                'url' => null,
                'proxy' => null,
                'transport' => null,
                'persistent' => true,
                'timeout' => null,
                'connections' => [], // host, port, path, timeout, transport, compression, persistent, timeout, username, password, config -> (curl, headers, url)
                'roundRobin' => false,
                'log' => false,
                'retryOnConflict' => 0,
                'bigintConversion' => false,
                'username' => null,
                'password' => null,
            ],
        ],
        'index_prefix' => env('SEARCH_INDEX_PREFIX', 'candy'),
        'index' => env('SEARCH_INDEX', 'candy_products_en'),

        /*
         * Here you can define the price aggregation break points, similar
         * to how it's done on Amazon.
         */
        'aggregation' => [
            'price' => [
                'ranges' => [
                    'low' => [5, 10, 20, 50],
                    'medium' => [200, 350, 400, 500],
                    'large' => [500, 600, 700, 800],
                ],
            ],
        ],
        /*
         * This is some experimental ranking, text searching has it's limits
         * and it's difficult to know what should be ranked higher .
         * Here you can define what fields have better "weight" on results.
         */
        'ranking' =>  [
            'categories' => [
                'multi_match' => [
                    'types' => [
                        'cross_fields' => [
                            'name^3',
                            'name.en^4',
                        ],
                    ],
                ],
            ],
            'products' => [
                'multi_match' => [
                    'types' => [
                        'cross_fields' => [
                            'name^3',
                            'name.en^4',
                            'tags^3',
                            'breadcrumbs.en^2',
                            'brand^2',
                            'sku^10',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
