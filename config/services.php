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
    'paypal' => [
        'host' => env('PAYPAL_HOST', ''),
        'client_id' => env('PAYPAL_CLIENT_ID', ''),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),

        /*
         * SDK configuration settings
         */
        'settings' => [

            /*
             * Payment Mode
             *
             * Available options are 'sandbox' or 'live'
             */
            'mode' => env('PAYPAL_ENV', 'sandbox'),

            'http.CURLOPT_SSLVERSION' => CURL_SSLVERSION_TLSv1,

            // Specify the max connection attempt (3000 = 3 seconds)
            'http.ConnectionTimeOut' => 3000,

            // Specify whether or not we want to store logs
            'log.LogEnabled' => true,

            // Specigy the location for our paypal logs
            'log.FileName' => storage_path().'/logs/paypal.log',

            /*
             * Log Level
             *
             * Available options: 'DEBUG', 'INFO', 'WARN' or 'ERROR'
             *
             * Logging is most verbose in the DEBUG level and decreases
             * as you proceed towards ERROR. WARN or ERROR would be a
             * recommended option for live environments.
             *
             */
            'log.LogLevel' => 'DEBUG',
        ],
    ],
    'sagepay' => [
        'vendor' => env('SAGEPAY_VENDOR'),
        'key' => env('SAGEPAY_KEY'),
        'password' => env('SAGEPAY_PASSWORD'),
    ],
];
