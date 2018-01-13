<?php

Route::group(['middleware' => 'auth:api'], function () {
    Route::resource('zones', 'ZonesController');
    Route::resource('menus', 'MenusController');
    Route::resource('dishes', 'DishesController');
    Route::resource('menus/{menu}/dishes', 'MenuDishesController');
    Route::resource('orders/{order}/menus', 'MenuOrdersController');
    Route::put('orders/{order}/send-to-kitchen', 'KitchenController@update');
    Route::resource('products/{product}/extras', 'ProductExtrasController');
    Route::resource('menu-dishes/{dishMenu}/products', 'MenuDishProductsController');
    Route::resource('menu-orders/{menuOrder}/products', 'MenuOrderProductsController');
});
