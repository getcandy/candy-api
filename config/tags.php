<?php

return [
    /*
     * Use any of the laravel string helpers or a custom function here
     * An array of functions will be called in the order they are defined, you can
     * use any valid callback providing it requires only one parameter
     */
    'format' => env('TAG_FORMAT', 'str_slug'),
];
