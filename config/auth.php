<?php

return [
    'guards' => [
        'api' => [
            'driver' => 'passport',
            'provider' => 'users',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => GetCandy\Api\Core\Auth\Models\User::class,
        ],
    ],
];
