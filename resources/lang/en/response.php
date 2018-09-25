<?php

return [
    'error' => [
        '200'            => 'You cannot error on a 200 response code',
        'unauthorized'   => 'Unauthorized',
        'wrong_args'     => 'Wrong arguments',
        'not_found'      => 'Resource not found',
        'internal'       => 'Internal server error',
        'forbidden'      => 'Forbidden',
        'invalid_lang'   => 'Invalid language code {:lang}',
        'minimum_record' => 'You must have at least 1 enabled record in the database',
        'expired'        => 'This resource has expired',
    ],
    'success' => [
        'sync' => ':element successfully synced',
    ],
    'token' => [
        'missing' => 'Missing authorization token from request',
        'expired' => 'Authorization token has expired',
        'invalid' => 'Authorization token is invalid',
    ],
];
