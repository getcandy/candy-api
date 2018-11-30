<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$this->get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes...
$this->group(['namespace' => 'Cms\\Auth'], function () {
    $this->get('login', 'LoginController@showLoginForm')->name('login');
    $this->post('login', 'LoginController@login');
    $this->get('logout', 'LoginController@logout')->name('logout');

    $this->get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    $this->post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    $this->get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    $this->post('password/reset', 'ResetPasswordController@reset');
});

/*
 * Hub Routes
 */
$this->group(['middleware' => ['hub', 'auth']], function () {
    $this->get('dashboard', [
        'as' => 'hub.index',
        'uses' => 'Cms\DashboardController@getIndex',
    ]);

    /*
     * Catalogue manager routes
     */
    $this->group(['prefix' => 'catalogue-manager', 'namespace' => 'Cms\\CatalogueManager'], function () {
        $this->get('products', [
            'as' => 'hub.products.index',
            'uses' => 'ProductsController@getIndex',
        ]);
        $this->get('products/{id}', [
            'as' => 'hub.products.edit',
            'uses' => 'ProductsController@getEdit',
        ]);
        $this->get('collections', [
            'as' => 'hub.collections.index',
            'uses' => 'CollectionsController@getIndex',
        ]);
        $this->get('collections/{id}', [
            'as' => 'hub.collections.edit',
            'uses' => 'CollectionsController@getEdit',
        ]);
        $this->get('categories', [
            'as' => 'hub.categories.index',
            'uses' => 'CategoriesController@getIndex',
        ]);
        $this->get('categories/{id}', [
            'as' => 'hub.categories.edit',
            'uses' => 'CategoriesController@getEdit',
        ]);
    });

    $this->group(['prefix' => 'order-processing', 'namespace' => 'Cms\\OrderProcessing'], function () {
        $this->get('orders', [
            'as' => 'hub.orders.index',
            'uses' => 'OrderController@getIndex',
        ]);
        $this->get('orders/{id}', [
            'as' => 'hub.orders.edit',
            'uses' => 'OrderController@getEdit',
        ]);
        $this->get('orders/{id}/invoice', [
            'as' => 'hub.orders.invoice',
            'uses' => 'OrderController@invoice',
        ]);
        $this->get('shipping-methods', [
            'as' => 'hub.shipping.index',
            'uses' => 'ShippingController@getIndex',
        ]);
        $this->get('shipping-methods/{id}', [
            'as' => 'hub.shipping.edit',
            'uses' => 'ShippingController@getEdit',
        ]);

        $this->get('shipping-zones', [
            'as' => 'hub.shipping.zones',
            'uses' => 'ShippingController@getZones',
        ]);
        $this->get('shipping-zones/{id}', [
            'as' => 'hub.shipping.zones.edit',
            'uses' => 'ShippingController@getZone',
        ]);

        // Customer routes
        $this->get('customers', [
            'as' => 'hub.customers.index',
            'uses' => 'CustomerController@getIndex',
        ]);
        $this->get('customers/{id}', [
            'as' => 'hub.customers.view',
            'uses' => 'CustomerController@getShow',
        ]);
    });

    $this->group(['prefix' => 'marketing-suite', 'namespace' => 'Cms\\MarketingSuite'], function () {
        $this->get('discounts', [
            'as' => 'hub.discounts.index',
            'uses' => 'DiscountController@getIndex',
        ]);
        $this->get('discounts/{id}', [
            'as' => 'hub.discounts.edit',
            'uses' => 'DiscountController@getEdit',
        ]);
    });
});
