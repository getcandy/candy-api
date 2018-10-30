<?php

use Illuminate\Http\Request;


use Dingo\Api\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// v1
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



    $api->group(['middleware' => ['auth:api']], function (Router $api) {

        // Rate: 100 requests per 5 minutes
        $api->group(['middleware' => ['api.throttle'], 'limit' => 100, 'expires' => 5], function (Router $api) {

            $api->post('account/password', [
                'as' => 'account.password.reset',
                'uses' => 'Auth\AccountController@resetPassword'
            ]);

            $api->post('auth/impersonate', [
                'as' => 'auth.impersonate',
                'uses' => 'Auth\ImpersonateController@process'
            ]);

            /**
             * Assets
             */

            $api->put('assets', 'Assets\AssetController@updateAll');
            $api->resource('assets', 'Assets\AssetController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Associations
             */

            $api->get('associations/groups', 'Associations\AssociationGroupController@index');
            /**
             * Attributes
             */
            $api->put('attributes/order', 'Attributes\AttributeController@reorder');
            $api->resource('attributes', 'Attributes\AttributeController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Attribute Groups
             */
            $api->put('attribute-groups/order', 'Attributes\AttributeGroupController@reorder');
            $api->resource('attribute-groups', 'Attributes\AttributeGroupController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Baskets
             */
            $api->post('baskets/resolve', 'Baskets\BasketController@resolve');
            $api->get('baskets/current', 'Baskets\BasketController@current');

            /**
             * Payments
             */
            $api->post('payments/{id}/refund', 'Payments\PaymentController@refund');
            $api->post('payments/{id}/void', 'Payments\PaymentController@void');

            /**
             * Categories
             */
            $api->get('categories/parent/{parentID?}', 'Categories\CategoryController@getByParent');
            $api->post('categories/reorder', 'Categories\CategoryController@reorder');

            $api->post('categories/{category}/routes', 'Categories\CategoryRouteController@store');
            $api->resource('categories', 'Categories\CategoryController', [
                'except' => ['index', 'edit', 'create', 'show']
            ]);

            /**
             * Channels
             */
            $api->resource('channels', 'Channels\ChannelController', [
                'except' => ['edit', 'create', 'show']
            ]);

            /**
             * Channels
             */
            $api->post('collections/{collection}/routes', 'Collections\CollectionRouteController@store');
            $api->resource('collections', 'Collections\CollectionController', [
                'except' => ['index', 'edit', 'create', 'show']
            ]);

            /**
             * Customers
             */

            $api->resource('customers/groups', 'Customers\CustomerGroupController', [
                'except' => ['edit', 'create', 'show']
            ]);

            $api->resource('customers', 'Customers\CustomerController', [
                'except' => ['edit', 'create', 'store']
            ]);

            /**
             * Discounts
             */
            $api->resource('discounts', 'Discounts\DiscountController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Languages
             */
            $api->resource('languages', 'Languages\LanguageController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Layouts
             */
            $api->resource('layouts', 'Layouts\LayoutController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Orders
             */
            $api->resource('orders', 'Orders\OrderController', [
                'only' => ['index', 'update']
            ]);

            /**
             * Pages
             */
            $api->get('/pages/{channel}/{lang}/{slug?}', 'Pages\PageController@show');
            $api->resource('pages', 'Pages\PageController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Product variants
             */
            $api->resource('products/variants', 'Products\ProductVariantController', [
                'except' => ['edit', 'create', 'store']
            ]);
            $api->post('products/{product}/variants', 'Products\ProductVariantController@store');

            /**
             * Products
             */
            $api->post('products/{product}/urls', 'Products\ProductController@createUrl');
            $api->post('products/{product}/redirects', 'Products\ProductRedirectController@store');
            $api->post('products/{product}/attributes', 'Products\ProductAttributeController@update');
            $api->post('products/{product}/collections', 'Products\ProductCollectionController@update');
            $api->post('products/{product}/routes', 'Products\ProductRouteController@store');
            $api->post('products/{product}/categories', 'Products\ProductCategoryController@update');
            $api->delete('products/{product}/categories/{category}', 'Products\ProductCategoryController@destroy');
            $api->delete('products/{product}/collections/{collection}', 'Products\ProductCollectionController@destroy');
            $api->post('products/{product}/associations', 'Products\ProductAssociationController@store');
            $api->delete('products/{product}/associations', 'Products\ProductAssociationController@destroy');
            $api->resource('products', 'Products\ProductController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Product families
             */
            $api->resource('product-families', 'Products\ProductFamilyController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Routes
             */
            $api->resource('routes', 'Routes\RouteController', [
                'except' => ['index', 'show', 'edit', 'create']
            ]);

            /**
             * Saved search
             */
            $api->post('saved-searches', 'Search\SavedSearchController@store');
            $api->delete('saved-searches/{id}', 'Search\SavedSearchController@destroy');
            $api->get('saved-searches/{type}', 'Search\SavedSearchController@getByType');

            /**
             * Shipping
             */
            $api->resource('shipping/zones', 'Shipping\ShippingZoneController', [
                'except' => ['edit', 'create']
            ]);
            $api->post('shipping/{id}/prices', 'Shipping\ShippingPriceController@store');
            $api->delete('shipping/prices/{id}', 'Shipping\ShippingPriceController@destroy');
            $api->put('shipping/prices/{id}', 'Shipping\ShippingPriceController@update');
            $api->put('shipping/{id}/zones', 'Shipping\ShippingMethodController@updateZones');
            $api->put('shipping/{id}/users', 'Shipping\ShippingMethodController@updateUsers');
            $api->delete('shipping/{id}/users/{user}', 'Shipping\ShippingMethodController@deleteUser');
            $api->resource('shipping', 'Shipping\ShippingMethodController', [
                'except' => ['index', 'edit', 'create']
            ]);

            /**
             * Tags
             */
            $api->resource('tags', 'Tags\TagController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Taxes
             */
            $api->resource('taxes', 'Taxes\TaxController', [
                'except' => ['edit', 'create']
            ]);

            /**
             * Users
             */
            $api->get('users/current', 'Users\UserController@getCurrentUser');
            $api->resource('users', 'Users\UserController', [
                'except' => ['create', 'store']
            ]);

            /**
             * Account
             */
            $api->post('account/password', [
                'as' => 'account.password.reset',
                'uses' => 'Auth\AccountController@resetPassword'
            ]);


        });

    });


});
//
//Route::group([
//    'middleware' => [
//        'auth:api',
//        'api.currency',
//        'api.customer_groups',
//        'api.locale',
//        'api.tax'
//    ],
//    'prefix' => 'api',
//    'namespace' => 'GetCandy\Api\Http\Controllers'
//], function ($router) {
//
//
//    $router->post('account/password', [
//        'as' => 'account.password.reset',
//        'uses' => 'Auth\AccountController@resetPassword'
//    ]);
//
//    $router->post('auth/impersonate', [
//        'as' => 'auth.impersonate',
//        'uses' => 'Auth\ImpersonateController@process'
//    ]);
//
//    /**
//     * Assets
//     */
//
//    $router->put('assets', 'Assets\AssetController@updateAll');
//    $router->resource('assets', 'Assets\AssetController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Associations
//     */
//
//    $router->get('associations/groups', 'Associations\AssociationGroupController@index');
//    /**
//     * Attributes
//     */
//    $router->put('attributes/order', 'Attributes\AttributeController@reorder');
//    $router->resource('attributes', 'Attributes\AttributeController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Attribute Groups
//     */
//    $router->put('attribute-groups/order', 'Attributes\AttributeGroupController@reorder');
//    $router->resource('attribute-groups', 'Attributes\AttributeGroupController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Baskets
//     */
//    $router->post('baskets/resolve', 'Baskets\BasketController@resolve');
//    $router->get('baskets/current', 'Baskets\BasketController@current');
//
//    /**
//     * Payments
//     */
//    $router->post('payments/{id}/refund', 'Payments\PaymentController@refund');
//    $router->post('payments/{id}/void', 'Payments\PaymentController@void');
//
//    /**
//     * Categories
//     */
//    $router->get('categories/parent/{parentID?}', 'Categories\CategoryController@getByParent');
//    $router->post('categories/reorder', 'Categories\CategoryController@reorder');
//
//    $router->post('categories/{category}/routes', 'Categories\CategoryRouteController@store');
//    $router->resource('categories', 'Categories\CategoryController', [
//        'except' => ['index', 'edit', 'create', 'show']
//    ]);
//
//    /**
//     * Channels
//     */
//    $router->resource('channels', 'Channels\ChannelController', [
//        'except' => ['edit', 'create', 'show']
//    ]);
//
//    /**
//     * Channels
//     */
//    $router->post('collections/{collection}/routes', 'Collections\CollectionRouteController@store');
//    $router->resource('collections', 'Collections\CollectionController', [
//        'except' => ['index', 'edit', 'create', 'show']
//    ]);
//
//    /**
//     * Customers
//     */
//
//    $router->resource('customers/groups', 'Customers\CustomerGroupController', [
//        'except' => ['edit', 'create', 'show']
//    ]);
//
//    $router->resource('customers', 'Customers\CustomerController', [
//        'except' => ['edit', 'create', 'store']
//    ]);
//
//    /**
//     * Discounts
//     */
//    $router->resource('discounts', 'Discounts\DiscountController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Languages
//     */
//    $router->resource('languages', 'Languages\LanguageController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Layouts
//     */
//    $router->resource('layouts', 'Layouts\LayoutController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Orders
//     */
//    $router->resource('orders', 'Orders\OrderController', [
//        'only' => ['index', 'update']
//    ]);
//
//    /**
//     * Pages
//     */
//    $router->get('/pages/{channel}/{lang}/{slug?}', 'Pages\PageController@show');
//    $router->resource('pages', 'Pages\PageController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Product variants
//     */
//    $router->resource('products/variants', 'Products\ProductVariantController', [
//        'except' => ['edit', 'create', 'store']
//    ]);
//    $router->post('products/{product}/variants', 'Products\ProductVariantController@store');
//
//    /**
//     * Products
//     */
//    $router->post('products/{product}/urls', 'Products\ProductController@createUrl');
//    $router->post('products/{product}/redirects', 'Products\ProductRedirectController@store');
//    $router->post('products/{product}/attributes', 'Products\ProductAttributeController@update');
//    $router->post('products/{product}/collections', 'Products\ProductCollectionController@update');
//    $router->post('products/{product}/routes', 'Products\ProductRouteController@store');
//    $router->post('products/{product}/categories', 'Products\ProductCategoryController@update');
//    $router->delete('products/{product}/categories/{category}', 'Products\ProductCategoryController@destroy');
//    $router->delete('products/{product}/collections/{collection}', 'Products\ProductCollectionController@destroy');
//    $router->post('products/{product}/associations', 'Products\ProductAssociationController@store');
//    $router->delete('products/{product}/associations', 'Products\ProductAssociationController@destroy');
//    $router->resource('products', 'Products\ProductController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Product families
//     */
//    $router->resource('product-families', 'Products\ProductFamilyController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Routes
//     */
//    $router->resource('routes', 'Routes\RouteController', [
//        'except' => ['index', 'show', 'edit', 'create']
//    ]);
//
//    /**
//     * Saved search
//     */
//    $router->post('saved-searches', 'Search\SavedSearchController@store');
//    $router->delete('saved-searches/{id}', 'Search\SavedSearchController@destroy');
//    $router->get('saved-searches/{type}', 'Search\SavedSearchController@getByType');
//
//    /**
//     * Shipping
//     */
//    $router->resource('shipping/zones', 'Shipping\ShippingZoneController', [
//        'except' => ['edit', 'create']
//    ]);
//    $router->post('shipping/{id}/prices', 'Shipping\ShippingPriceController@store');
//    $router->delete('shipping/prices/{id}', 'Shipping\ShippingPriceController@destroy');
//    $router->put('shipping/prices/{id}', 'Shipping\ShippingPriceController@update');
//    $router->put('shipping/{id}/zones', 'Shipping\ShippingMethodController@updateZones');
//    $router->put('shipping/{id}/users', 'Shipping\ShippingMethodController@updateUsers');
//    $router->delete('shipping/{id}/users/{user}', 'Shipping\ShippingMethodController@deleteUser');
//    $router->resource('shipping', 'Shipping\ShippingMethodController', [
//        'except' => ['index', 'edit', 'create']
//    ]);
//
//    /**
//     * Tags
//     */
//    $router->resource('tags', 'Tags\TagController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Taxes
//     */
//    $router->resource('taxes', 'Taxes\TaxController', [
//        'except' => ['edit', 'create']
//    ]);
//
//    /**
//     * Users
//     */
//    $router->get('users/current', 'Users\UserController@getCurrentUser');
//    $router->resource('users', 'Users\UserController', [
//        'except' => ['create', 'store']
//    ]);
//
//    /**
//     * Account
//     */
//    $router->post('account/password', [
//        'as' => 'account.password.reset',
//        'uses' => 'Auth\AccountController@resetPassword'
//    ]);
//});
