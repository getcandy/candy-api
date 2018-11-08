<?php


use Dingo\Api\Routing\Router;

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', [
    'namespace' => 'GetCandy\Api\Http\Controllers',
    'middleware' => ['api',
        'api.currency',
        'api.customer_groups',
        'api.locale',
        'api.tax'
    ]
], function (Router $api) {

    $api->group(['middleware' => ['api.throttle'], 'limit' => 100000, 'expires' => 5], function (Router $api) {
        $api->group(['prefix' => 'platform'], function (Router $api) {
            //
        });
    });


    // Rate: 100 requests per 5 minutes
    $api->group(['middleware' => ['api.throttle'], 'limit' => 100, 'expires' => 5], function (Router $api) {
        $api->group(['middleware' => ['auth:api']], function (Router $api) {
// $router->get('channels', 'Channels\ChannelController@index');
            $api->get('channels/{id}', 'Channels\ChannelController@show');
            $api->get('collections', 'Collections\CollectionController@index');
            $api->get('collections/{id}', 'Collections\CollectionController@show');
            $api->get('categories/{id}', 'Categories\CategoryController@show');
            $api->get('products/{product}', 'Products\ProductController@show');
            $api->post('customers', 'Customers\CustomerController@store');
            $api->get('products', 'Products\ProductController@index');

            /**
             * Baskets
             */
            $api->get('baskets', 'Products\ProductController@index');
            $api->put('baskets/{id}/discounts', 'Baskets\BasketController@addDiscount');
            $api->delete('baskets/{id}/discounts', 'Baskets\BasketController@deleteDiscount');
            $api->put('baskets/{id}/user', 'Baskets\BasketController@putUser');
            $api->delete('baskets/{id}/user', 'Baskets\BasketController@deleteUser');
            $api->resource('baskets', 'Baskets\BasketController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Categories
             */
            $api->get('categories', 'Categories\CategoryController@index');

            /**
             * Countries
             */
            $api->get('countries', 'Countries\CountryController@index');

            /**
             * Currencies
             */
            $api->resource('currencies', 'Currencies\CurrencyController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Customers
             */
            $api->resource('customers', 'Customers\CustomerController', [
                'except' => ['index', 'edit', 'create', 'show']
            ]);

            /**
             * Orders
             */

            $api->post('orders/process', 'Orders\OrderController@process');
            $api->post('orders/{id}/expire', 'Orders\OrderController@expire');
            $api->put('orders/{id}/shipping/address', 'Orders\OrderController@shippingAddress');
            $api->put('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethod');
            $api->get('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethods');
            $api->put('orders/{id}/shipping/cost', 'Orders\OrderController@shippingCost');
            $api->put('orders/{id}/contact', 'Orders\OrderController@addContact');
            $api->put('orders/{id}/billing/address', 'Orders\OrderController@billingAddress');
            $api->resource('orders', 'Orders\OrderController', [
                'only' => ['store', 'show']
            ]);
            $api->get('orders/{id}/invoice', 'Orders\OrderController@invoice');

            /**
             * Payments
             */
            $api->get('payments/provider', 'Payments\PaymentController@provider');
            $api->get('payments/types', 'Payments\PaymentTypeController@index');

            $api->get('routes', 'Routes\RouteController@index');
            $api->get('routes/{slug}', [
                'uses' => 'Routes\RouteController@show'
            ])->where(['slug' => '.*']);


            $api->post('password/reset', 'Auth\ResetPasswordController@reset');
            $api->post('password/reset/request', 'Auth\ForgotPasswordController@sendResetLinkEmail');

            $api->get('search', 'Search\SearchController@search');
            $api->get('search/products', 'Search\SearchController@products');

            /**
             * Shipping
             */
            $api->get('shipping', 'Shipping\ShippingMethodController@index');


            $api->post('users', 'Users\UserController@store');
            $api->post('users/{userid}', 'Users\UserController@update');
        });
    });
});


//Route::group([
//    'middleware' => [
//        'api.client',
//        'api.currency',
//        'api.customer_groups',
//        'api.locale',
//        'api.tax'
//    ],
//    'prefix' => 'api/' . config('app.api_version', 'v1'),
//    'namespace' => 'GetCandy\Api\Http\Controllers'
//], function ($router) {
//    /*
//|--------------------------------------------------------------------------
//| API Client Routes
//|--------------------------------------------------------------------------
//|
//| Here is where you can register API Client routes for GetCandy
//| These are READ ONLY routes
//|
//     */
//// $router->get('channels', 'Channels\ChannelController@index');
//    $router->get('channels/{id}', 'Channels\ChannelController@show');
//    $router->get('collections', 'Collections\CollectionController@index');
//    $router->get('collections/{id}', 'Collections\CollectionController@show');
//    $router->get('categories/{id}', 'Categories\CategoryController@show');
//    $router->get('products/{product}', 'Products\ProductController@show');
//    $router->post('customers', 'Customers\CustomerController@store');
//    $router->get('products', 'Products\ProductController@index');
//
//    /**
//     * Baskets
//     */
//    $router->get('baskets', 'Products\ProductController@index');
//    $router->put('baskets/{id}/discounts', 'Baskets\BasketController@addDiscount');
//    $router->delete('baskets/{id}/discounts', 'Baskets\BasketController@deleteDiscount');
//    $router->put('baskets/{id}/user', 'Baskets\BasketController@putUser');
//    $router->delete('baskets/{id}/user', 'Baskets\BasketController@deleteUser');
//    $router->resource('baskets', 'Baskets\BasketController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Categories
//     */
//    $router->get('categories', 'Categories\CategoryController@index');
//
//    /**
//     * Countries
//     */
//    $router->get('countries', 'Countries\CountryController@index');
//
//    /**
//     * Currencies
//     */
//    $router->resource('currencies', 'Currencies\CurrencyController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Customers
//     */
//    $router->resource('customers', 'Customers\CustomerController', [
//        'except' => ['index', 'edit', 'create', 'show']
//    ]);
//
//    /**
//     * Orders
//     */
//
//    $router->post('orders/process', 'Orders\OrderController@process');
//    $router->post('orders/{id}/expire', 'Orders\OrderController@expire');
//    $router->put('orders/{id}/shipping/address', 'Orders\OrderController@shippingAddress');
//    $router->put('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethod');
//    $router->get('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethods');
//    $router->put('orders/{id}/shipping/cost', 'Orders\OrderController@shippingCost');
//    $router->put('orders/{id}/contact', 'Orders\OrderController@addContact');
//    $router->put('orders/{id}/billing/address', 'Orders\OrderController@billingAddress');
//    $router->resource('orders', 'Orders\OrderController', [
//        'only' => ['store', 'show']
//    ]);
//    $router->get('orders/{id}/invoice', 'Orders\OrderController@invoice');
//
//    /**
//     * Payments
//     */
//    $router->get('payments/provider', 'Payments\PaymentController@provider');
//    $router->get('payments/types', 'Payments\PaymentTypeController@index');
//
//    $router->get('routes', 'Routes\RouteController@index');
//    $router->get('routes/{slug}', [
//        'uses' => 'Routes\RouteController@show'
//    ])->where(['slug' => '.*']);
//
//
//    $router->post('password/reset', 'Auth\ResetPasswordController@reset');
//    $router->post('password/reset/request', 'Auth\ForgotPasswordController@sendResetLinkEmail');
//
//    $router->get('search', 'Search\SearchController@search');
//    $router->get('search/products', 'Search\SearchController@products');
//
//    /**
//     * Shipping
//     */
//    $router->get('shipping', 'Shipping\ShippingMethodController@index');
//
//
//    $router->post('users', 'Users\UserController@store');
//    $router->post('users/{userid}', 'Users\UserController@update');
//});