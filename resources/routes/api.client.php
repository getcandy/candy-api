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
// $this->get('channels', 'Channels\ChannelController@index');
$this->get('channels/{id}', 'Channels\ChannelController@show');
$this->get('collections', 'Collections\CollectionController@index');
$this->get('collections/{id}', 'Collections\CollectionController@show');
$this->get('categories/{id}', 'Categories\CategoryController@show');
$this->get('products/{product}', 'Products\ProductController@show');
$this->post('customers', 'Customers\CustomerController@store');
$this->get('products', 'Products\ProductController@index');

/**
 * Categories
 */
$this->get('categories', 'Categories\CategoryController@index');

/**
 * Customers
 */
$this->resource('customers', 'Customers\CustomerController', [
    'except' => ['index', 'edit', 'create', 'show']
]);
$this->get('customers/groups', 'Customers\CustomerGroupController@index');

/**
 * Users
 */
$this->post('users/{user}', 'Users\UserController@update');

$this->get('routes', 'Routes\RouteController@index');
$this->get('routes/{slug}', [
    'uses' => 'Routes\RouteController@show'
])->where(['slug' => '.*']);


$this->post('password/reset', 'Auth\ResetPasswordController@reset');
$this->post('password/reset/request', 'Auth\ForgotPasswordController@sendResetLinkEmail');

$this->get('search', 'Search\SearchController@search');
$this->get('search/products', 'Search\SearchController@products');
