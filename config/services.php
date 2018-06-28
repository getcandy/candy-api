<?php

return [
    'braintree' => [
        'key' => env('BRAINTREE_PUBLIC_KEY'),
        'secret' => env('BRAINTREE_PRIVATE_KEY'),
        '3D_secure' => env('3D_SECURE', false),
        'merchant_id' => env('BRAINTREE_MERCHANT'),
        'merchants' => [
            'default' => env('BRAINTREE_GBP_MERCHANT'),
            'eur' => env('BRAINTREE_EUR_MERCHANT'),
        ],
    ],
    'sagepay' => [
        'key' => env('SAGEPAY_KEY'),
        'password' => env('SAGEPAY_PASSWORD'),
    ]
];
