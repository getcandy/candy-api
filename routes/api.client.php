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

$router->get('/', '\GetCandy\Api\Core\Root\Actions\FetchRoot');

$router->get('channels/{encoded_id}', '\GetCandy\Api\Core\Channels\Actions\FetchChannel');
$router->get('collections', 'Collections\CollectionController@index');
$router->get('collections/{id}', 'Collections\CollectionController@show');
$router->get('categories/{id}', 'Categories\CategoryController@show');
$router->get('products/recommended', 'Products\ProductController@recommended');
$router->get('products/{product}', 'Products\ProductController@show');
$router->get('products', 'Products\ProductController@index');

/*
* Customers
*/
$router->group([
    'prefix' => 'customers',
], function ($group) {
    $group->get('{encoded_id}', '\GetCandy\Api\Core\Customers\Actions\FetchCustomer');
    $group->put('{encoded_id}', '\GetCandy\Api\Core\Customers\Actions\UpdateCustomer');
    $group->post('/', '\GetCandy\Api\Core\Customers\Actions\CreateCustomer');
});

/*
    * Baskets
    */
// $router->get('baskets', 'Products\ProductController@index');
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
$router->get('countries', '\GetCandy\Api\Core\Countries\Actions\FetchCountries');

/*
* Languages
*/
$router->group([
    'prefix' => 'languages',
], function ($group) {
    $group->get('/', '\GetCandy\Api\Core\Languages\Actions\FetchLanguages');
    $group->get('/{encoded_id}', '\GetCandy\Api\Core\Languages\Actions\FetchLanguage');
});

/*
    * Currencies
    */
$router->resource('currencies', 'Currencies\CurrencyController', [
    'except' => ['edit', 'create'],
]);

/*
    * Orders
    */

$router->post('orders/process', 'Orders\OrderController@process');
$router->post('orders/{id}/expire', 'Orders\OrderController@expire');
$router->put('orders/{id}/shipping/address', 'Orders\OrderController@shippingAddress');
// $router->put('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethod');
$router->get('orders/{id}/shipping/methods', 'Orders\OrderController@shippingMethods');
$router->put('orders/{id}/shipping/cost', 'Orders\OrderController@shippingCost');
$router->put('orders/{id}/contact', 'Orders\OrderController@addContact');
$router->put('orders/{id}/billing/address', 'Orders\OrderController@billingAddress');
$router->get('orders/types', 'Orders\OrderController@getTypes');
$router->post('orders/{id}/lines', 'Orders\OrderLineController@store');
$router->delete('orders/lines/{id}', 'Orders\OrderLineController@destroy');

$router->resource('orders', 'Orders\OrderController', [
    'only' => ['store', 'show'],
]);

/*
    * Payments
    */
$router->post('payments/3d-secure', 'Payments\PaymentController@validateThreeD');
$router->get('payments/provider', 'Payments\PaymentController@provider');
$router->get('payments/providers', 'Payments\PaymentController@providers');
$router->get('payments/types', 'Payments\PaymentTypeController@index');

/*
* Routes
*/
$router->group([
    'prefix' => 'routes',
], function ($route) {
    $route->get('search', '\GetCandy\Api\Core\Routes\Actions\SearchForRoute');
    $route->get('{encoded_id}', '\GetCandy\Api\Core\Routes\Actions\FetchRoute');
});

$router->get('search', '\GetCandy\Api\Core\Search\Actions\Search');

// $router->get('search', 'Search\SearchController@search');
// $router->get('search/sku', 'Search\SearchController@sku');
// $router->get('search/products', 'Search\SearchController@products');
/*
    * Shipping
    */
$router->get('shipping', 'Shipping\ShippingMethodController@index');
$router->get('shipping/prices/estimate', 'Shipping\ShippingPriceController@estimate');

/*
 * Users
 */
$router->post('users', '\GetCandy\Api\Core\Users\Actions\CreateUser');

$router->get('plugins', 'Plugins\PluginController@index');
