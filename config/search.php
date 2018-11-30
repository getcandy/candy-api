<?php

return [
    'client' => \GetCandy\Api\Core\Search\Providers\Elastic\Elastic::class,
    'index_prefix' => env('SEARCH_INDEX_PREFIX', 'candy'),
    'index' => env('SEARCH_INDEX'),
    'algolia' => [
        'app_key' => env('ALGOLIA_KEY'),
        'app_id' => env('ALGOLIA_ID'),
    ],
];
