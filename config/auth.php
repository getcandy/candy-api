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
            'model' => GetCandy\Api\Auth\Models\User::class,
        ],
    ],
];
