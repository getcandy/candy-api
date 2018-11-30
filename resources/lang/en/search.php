<?php

return [
    'price' => [
        'aggregation' => [
            'less_then' => 'Under :max',
            'between' => ':min - :max',
            'over' => 'Over :min',
        ],
    ],
];
