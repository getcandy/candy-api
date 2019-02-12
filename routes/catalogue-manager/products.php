<?php

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
