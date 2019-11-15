<?php

    /*
    * Imports
    */
    $router->post('import', 'Utils\ImportController@process');

    $router->post('account/password', [
        'as' => 'account.password.reset',
        'uses' => 'Auth\AccountController@resetPassword',
    ]);

    $router->get('activity-log', [
        'as' => 'activitylog.index',
        'uses' => 'ActivityLog\ActivityLogController@index',
    ]);

    $router->post('activity-log', [
        'as' => 'activitylog.store',
        'uses' => 'ActivityLog\ActivityLogController@store',
    ]);

    $router->post('addresses', 'Addresses\AddressController@store');
    $router->post('auth/impersonate', [
        'as' => 'auth.impersonate',
        'uses' => 'Auth\ImpersonateController@process',
    ]);

    /*
     * Assets
     */

    $router->put('assets', 'Assets\AssetController@updateAll');
    $router->post('assets/simple', 'Assets\AssetController@storeSimple');
    $router->resource('assets', 'Assets\AssetController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Associations
     */

    $router->get('associations/groups', 'Associations\AssociationGroupController@index');
    /*
     * Attributes
     */
    $router->put('attributes/order', 'Attributes\AttributeController@reorder');
    $router->resource('attributes', 'Attributes\AttributeController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Attribute Groups
     */
    $router->put('attribute-groups/order', 'Attributes\AttributeGroupController@reorder');
    $router->resource('attribute-groups', 'Attributes\AttributeGroupController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Baskets
     */
    Route::group(['middleware', ['api:channels']], function ($router) {
        $router->post('baskets/resolve', 'Baskets\BasketController@resolve');
        $router->get('baskets/current', 'Baskets\BasketController@current');
        $router->get('baskets/saved', 'Baskets\BasketController@saved');
        $router->post('baskets/{id}/save', 'Baskets\BasketController@save');
        $router->post('baskets/{id}/claim', 'Baskets\BasketController@claim');
        $router->delete('baskets/{basket}', 'Baskets\BasketController@destroy');
        $router->put('baskets/saved/{basket}', 'Baskets\SavedBasketController@update');
    });

    /*
     * Payments
     */
    $router->post('payments/{id}/refund', 'Payments\PaymentController@refund');
    $router->post('payments/{id}/void', 'Payments\PaymentController@void');

    /*
     * Categories
     */
    $router->get('categories/parent/{parentID?}', 'Categories\CategoryController@getByParent');
    $router->post('categories/reorder', 'Categories\CategoryController@reorder');
    $router->post('categories/{category}/products/attach', 'Products\ProductCategoryController@attach');
    $router->put('categories/{category}/products', 'Categories\CategoryController@putProducts');
    $router->put('categories/{category}/layouts', 'Categories\LayoutController@store');

    $router->post('categories/{category}/routes', 'Categories\CategoryRouteController@store');
    $router->resource('categories', 'Categories\CategoryController', [
        'except' => ['index', 'edit', 'create', 'show'],
    ]);

    /*
     * Channels
     */
    $router->resource('channels', 'Channels\ChannelController', [
        'except' => ['edit', 'create', 'show'],
    ]);

    /*
     * Channels
     */
    $router->post('collections/{collection}/routes', 'Collections\CollectionRouteController@store');
    $router->put('collections/{collection}/products', 'Collections\CollectionProductController@store');
    $router->resource('collections', 'Collections\CollectionController', [
        'except' => ['index', 'edit', 'create', 'show'],
    ]);

    /*
     * Customers
     */
    $router->resource('customers/groups', 'Customers\CustomerGroupController', [
        'except' => ['edit', 'create', 'show'],
    ]);

    $router->resource('customers', 'Customers\CustomerController', [
        'except' => ['edit', 'create', 'store'],
    ]);

    /*
     * Discounts
     */
    $router->resource('discounts', 'Discounts\DiscountController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Languages
     */
    $router->resource('languages', 'Languages\LanguageController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Layouts
     */
    $router->resource('layouts', 'Layouts\LayoutController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Orders
     */
    $router->post('orders/bulk', 'Orders\OrderController@bulkUpdate');
    $router->get('orders/types', 'Orders\OrderController@getTypes');
    $router->get('orders/export', 'Orders\OrderController@getExport');
    $router->post('orders/email-preview/{status}', 'Orders\OrderController@emailPreview');
    $router->resource('orders', 'Orders\OrderController', [
        'only' => ['index', 'update'],
    ]);

    // /*
    //  * Pages
    //  */
    // $router->get('/pages/{channel}/{lang}/{slug?}', 'Pages\PageController@show');
    // $router->resource('pages', 'Pages\PageController', [
    //     'except' => ['edit', 'create'],
    // ]);

    /*
     * Product variants
     */
    $router->resource('products/variants', 'Products\ProductVariantController', [
        'except' => ['edit', 'create', 'store'],
    ]);
    $router->put('products/variants/{variant}/inventory', 'Products\ProductVariantController@updateInventory');
    $router->post('products/{product}/variants', 'Products\ProductVariantController@store');
    $router->post('products/{product}/duplicate', 'Products\ProductController@duplicate');

    /*
     * Products
     */
    $router->prefix('products')->namespace('Products')->group(function ($router) {
        $router->post('/{product}/urls', 'ProductController@createUrl');
        $router->post('/{product}/redirects', 'ProductRedirectController@store');
        $router->post('/{product}/attributes', 'ProductAttributeController@update');
        $router->post('/{product}/collections', 'ProductCollectionController@update');
        $router->post('/{product}/routes', 'ProductRouteController@store');
        $router->post('/{product}/categories', 'ProductCategoryController@update');
        $router->post('/{product}/channels', 'ProductChannelController@store');
        $router->delete('/{product}/categories/{category}', 'ProductCategoryController@destroy');
        $router->delete('/{product}/collections/{collection}', 'ProductCollectionController@destroy');
        $router->post('/{product}/associations', 'ProductAssociationController@store');
        $router->delete('/{product}/associations', 'ProductAssociationController@destroy');

        /*
        * Updates
        */
        $router->post('/{product}/customer-groups', 'ProductCustomerGroupController@store');
    });

    /*
     * Reporting
     */

    $router->prefix('reports')->namespace('Reports')->group(function ($router) {
        $router->get('/sales', 'ReportController@sales');
        $router->get('/orders', 'ReportController@orders');
        $router->get('/metrics/{subject}', 'ReportController@metrics');
    });

    /*
     * Resource routes
     */
    $router->resource('products', 'Products\ProductController', [
        'except' => ['edit', 'create', 'show'],
    ]);

    /*
     * Product families
     */
    $router->resource('product-families', 'Products\ProductFamilyController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Routes
     */
    $router->resource('routes', 'Routes\RouteController', [
        'except' => ['index', 'show', 'edit', 'create'],
    ]);

    /*
     * Saved search
     */
    $router->post('saved-searches', 'Search\SavedSearchController@store');
    $router->delete('saved-searches/{id}', 'Search\SavedSearchController@destroy');
    $router->get('saved-searches/{type}', 'Search\SavedSearchController@getByType');

    /*
     * Settings
     */
    $router->get('settings/{handle}', 'Settings\SettingController@show');

    /*
     * Shipping
     */
    $router->resource('shipping/zones', 'Shipping\ShippingZoneController', [
        'except' => ['edit', 'create'],
    ]);
    $router->post('shipping/{id}/prices', 'Shipping\ShippingPriceController@store');
    $router->delete('shipping/prices/{id}', 'Shipping\ShippingPriceController@destroy');
    $router->put('shipping/prices/{id}', 'Shipping\ShippingPriceController@update');
    $router->put('shipping/{id}/zones', 'Shipping\ShippingMethodController@updateZones');
    $router->put('shipping/{id}/users', 'Shipping\ShippingMethodController@updateUsers');
    $router->delete('shipping/{id}/users/{user}', 'Shipping\ShippingMethodController@deleteUser');
    $router->resource('shipping', 'Shipping\ShippingMethodController', [
        'except' => ['index', 'edit', 'create'],
    ]);

    /*
     * Tags
     */
    $router->resource('tags', 'Tags\TagController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Taxes
     */
    $router->resource('taxes', 'Taxes\TaxController', [
        'except' => ['edit', 'create'],
    ]);

    /*
     * Users
     */
    $router->get('users/fields', 'Users\UserController@fields');
    $router->get('users/current', 'Users\UserController@getCurrentUser');
    $router->delete('users/payments/{id}', 'Users\UserController@deleteReusablePayment');
    $router->resource('users', 'Users\UserController', [
        'except' => ['create', 'store'],
    ]);

    /*
     * Account
     */
    $router->post('account/password', [
        'as' => 'account.password.reset',
        'uses' => 'Auth\AccountController@resetPassword',
    ]);
