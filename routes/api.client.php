<?php

/*
|--------------------------------------------------------------------------
| API Client Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API Client routes for GetCandy
| These are READ ONLY routes
|
*/

$router->get('/', function () {
    $channel = app()->make(GetCandy\Api\Core\Channels\Interfaces\ChannelFactoryInterface::class);
    $currency = app()->make(GetCandy\Api\Core\Currencies\Interfaces\CurrencyConverterInterface::class);

    return response()->json([
        'version' => GetCandy\Api\Core\GetCandy::version(),
        'locale' => app()->getLocale(),
        'channel' => new GetCandy\Api\Http\Resources\Channels\ChannelResource($channel->getChannel()),
        'currency' => new GetCandy\Api\Http\Resources\Currencies\CurrencyResource($currency->get()),
    ]);
});

// Address Route
$router->delete('addresses/{id}', 'Addresses\AddressController@destroy');
$router->put('addresses/{id}', 'Addresses\AddressController@update');
$router->post('addresses/{id}/default', 'Addresses\AddressController@makeDefault');
$router->post('addresses/{id}/default/remove', 'Addresses\AddressController@removeDefault');

// $router->get('channels', 'Channels\ChannelController@index');
$router->get('channels/{id}', 'Channels\ChannelController@show');
$router->get('collections', 'Collections\CollectionController@index');
$router->get('collections/{id}', 'Collections\CollectionController@show');
$router->get('categories/{id}', 'Categories\CategoryController@show');
$router->get('products/recommended', 'Products\ProductController@recommended');
$router->get('products/{product}', 'Products\ProductController@show');
$router->post('customers', 'Customers\CustomerController@store');
$router->get('products', 'Products\ProductController@index');

/*
    * Baskets
    */
$router->get('baskets', 'Products\ProductController@index');
$router->post('baskets/{id}/meta', 'Baskets\BasketController@addMeta');
$router->put('baskets/{id}/discounts', 'Baskets\BasketController@addDiscount');
$router->delete('baskets/{id}/discounts', 'Baskets\BasketController@deleteDiscount');
$router->put('baskets/{id}/user', 'Baskets\BasketController@putUser');
$router->delete('baskets/{id}/user', 'Baskets\BasketController@deleteUser');
$router->resource('baskets', 'Baskets\BasketController', [
    'except' => ['edit', 'create', 'destroy', 'update'],
]);

/*
    * Basket Lines
    */
$router->post('basket-lines', 'Baskets\BasketLineController@store');
$router->put('basket-lines/{id}', 'Baskets\BasketLineController@update');
$router->post('basket-lines/{id}/add', 'Baskets\BasketLineController@addQuantity');
$router->post('basket-lines/{id}/remove', 'Baskets\BasketLineController@removeQuantity');
$router->delete('basket-lines', 'Baskets\BasketLineController@destroy');

/*
    * Categories
    */
$router->get('categories', 'Categories\CategoryController@index');
$router->get('categories/{category}/children', 'Categories\CategoryController@children');

/*
    * Countries
    */
$router->get('countries', 'Countries\CountryController@index');

/*
    * Currencies
    */
$router->resource('currencies', 'Currencies\CurrencyController', [
    'except' => ['edit', 'create'],
]);

/*
    * Customers
    */
$router->resource('customers', 'Customers\CustomerController', [
    'except' => ['index', 'edit', 'create', 'show'],
]);

/*
    * Orders
    */

$router->post('orders/process', 'Orders\OrderController@process');
$router->post('orders/{id}/expire', 'Orders\OrderController@expire');
$router->put('orders/{id}/shipping/address', 'Orders\OrderController@shippingAddress');
$router->put('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethod');
$router->get('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethods');
$router->put('orders/{id}/shipping/cost', 'Orders\OrderController@shippingCost');
$router->put('orders/{id}/contact', 'Orders\OrderController@addContact');
$router->put('orders/{id}/billing/address', 'Orders\OrderController@billingAddress');

$router->post('orders/{id}/lines', 'Orders\OrderLineController@store');
$router->delete('orders/lines/{id}', 'Orders\OrderLineController@destroy');

$router->resource('orders', 'Orders\OrderController', [
    'only' => ['store', 'show'],
]);
$router->get('orders/{id}/invoice', 'Orders\OrderController@invoice');

/*
    * Payments
    */
$router->post('payments/3d-secure', 'Payments\PaymentController@validateThreeD');
$router->get('payments/provider', 'Payments\PaymentController@provider');
$router->get('payments/providers', 'Payments\PaymentController@providers');
$router->get('payments/types', 'Payments\PaymentTypeController@index');

$router->get('routes', 'Routes\RouteController@index');
$router->get('routes/{slug}', [
    'uses' => 'Routes\RouteController@show',
])->where(['slug' => '.*']);

$router->post('password/reset', 'Auth\ResetPasswordController@reset');
$router->post('password/reset/request', 'Auth\ForgotPasswordController@sendResetLinkEmail');

$router->get('search', 'Search\SearchController@search');
$router->get('search/suggest', 'Search\SearchController@suggest');
$router->get('search/sku', 'Search\SearchController@sku');
$router->get('search/products', 'Search\SearchController@products');

/*
    * Shipping
    */
$router->get('shipping', 'Shipping\ShippingMethodController@index');
$router->get('shipping/prices/estimate', 'Shipping\ShippingPriceController@estimate');

$router->post('users', 'Users\UserController@store');
$router->post('users/{userid}', 'Users\UserController@update');

$router->get('plugins', 'Plugins\PluginController@index');
