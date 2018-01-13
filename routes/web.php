<?php

Route::group(['middleware' => 'only-admin'], function () {
    Route::get('/menus/{menu}/products', function(\App\Entities\Menu $menu){
        $menu->load('dishes');
        return view('menus.products', compact('menu'));
    });

    Route::resource('zones', 'ZonesController');
    Route::resource('menus', 'MenusController');
    Route::resource('extras', 'ExtrasController');
    Route::resource('dishes', 'DishesController');
    Route::resource('tables', 'TablesController');
    Route::resource('kitchens', 'KitchensController');
    Route::resource('combinations', 'CombinationsController');
    Route::resource('menus/{menu}/combinations', 'MenuCombinationController');
    Route::resource('combinations/{combination}/dishes', 'DishCombinationController');
    Route::resource('delivery-notes', 'DeliveryNotesController', ['parameters' => ['delivery-notes' => 'documentId']]);
    Route::get('reports/allergens', 'ReportsController@allergens');

    /** P */
    Route::resource('products', 'ProductsController');
    Route::get('/products/{product}/allergens', 'ProductsController@allergens');
    Route::get('/products/{product}/customers', 'ProductsController@customers');
    Route::get('/products/{product}/extras', 'ProductsController@extras');
});