<?php

/*
 * This file is part of Laravel Hashids.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

$alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';

return [

    /*
    |--------------------------------------------------------------------------
    | Default Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the connections below you wish to use as
    | your default connection for all work. Of course, you may use many
    | connections at once using the manager class.
    |
    */

    'default' => 'main',

    /*
    |--------------------------------------------------------------------------
    | Hashids Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the connections setup for your application. Example
    | configuration has been included, but you may add as many connections as
    | you would like.
    |
    */

    'connections' => [
        'main' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_SALT', 'changeme'),
            'length' => 8,
        ],
        'attribute' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_ATTRIBUTE_SALT', 'attribute'),
            'length' => 8,
        ],
        'attribute_group' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_ATTRIBUTE_GROUP_SALT', 'attribute_group'),
            'length' => 8,
        ],
        'channel' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_CHANNEL_SALT', 'channel'),
            'length' => 8,
        ],
        'checkout' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_CHECKOUT_SALT', 'checkout'),
        ],
        'basket' => [
            'alphabet' => $alphabet,
            'length' => 15,
            'salt' => env('HASHIDS_BASKET_SALT', 'basket'),
        ],
        'currency' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_CURRENCY_SALT', 'currency'),
            'length' => 8,
        ],
        'language' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_LANGUAGE_SALT', 'language'),
            'length' => 8,
        ],
        'order' => [
            'alphabet' => $alphabet,
            'length' => 15,
            'salt' => env('HASHIDS_ORDER_SALT', 'order'),
        ],
        'product' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_PRODUCT_SALT', 'product'),
            'length' => 8,
        ],
        'product_attribute' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_PRODUCT_ATTRIBUTE_SALT', 'product_attribute'),
            'length' => 8,
        ],
        'product_family' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_PRODUCT_FAMILY_SALT', 'product_family'),
            'length' => 8,
        ],
        'product_category' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_PRODUCT_CATEGORY_SALT', 'product_category'),
            'length' => 8,
        ],
        'tag' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_ATTRIBUTE_SALT', 'attribute'),
            'length' => 8,
        ],
        'tax' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_TAX_SALT', 'tax'),
            'length' => 8,
        ],
        'user' => [
            'alphabet' => $alphabet,
            'salt' => env('HASHIDS_USER_SALT', 'user'),
            'length' => 8,
        ],
    ],
];
