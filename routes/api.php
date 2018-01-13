<?php

Route::group(['middleware' => 'auth:api'], function () {
    /** C */
    Route::resource('cash', 'CashController', ['parameters' => ['cash' => 'order']]);
    Route::get('categories/{category}/products', 'CategoryProductsController@index');

    /** D */
    Route::resource('default-delivery-notes', 'DefaultDeliveryNotesController');
    Route::resource('delivery-notes', 'DeliveryNotesController');
    Route::post('delivery-notes/invoices', 'DeliveryNotesToInvoiceController@store');
    Route::resource('dishes', 'DishesController');

    /** E */
    Route::resource('expediture-delivery-notes', 'ExpeditureDeliveryNotesController');

    /** I */
    Route::resource('invoices', 'InvoicesController');

    /** L */
    Route::resource('lines', 'LinesController');

    /** M */
    Route::resource('menu-dishes/{dishMenu}/products', 'MenuDishProductsController');
    Route::resource('menu-orders/{menuOrder}/products', 'MenuOrderProductsController');
    Route::resource('menus', 'MenusController');
    Route::resource('menus/{menu}/dishes', 'MenuDishesController');

    /** O */
    Route::resource('orders-tpv', 'OrdersTpvController');
    Route::resource('orders', 'OrdersController');
    Route::resource('orders/{order}/menus', 'MenuOrdersController');
    Route::put('orders/{order}/send-to-kitchen', 'KitchenController@update');

    /** P */
    Route::get('products', 'ProductsController@index');
    Route::post('products', 'ProductsController@updateStock');
    Route::resource('products/{product}/allergens', 'ProductAllergensController');
    Route::resource('products/{product}/customers', 'ProductCustomersController');
    Route::resource('products/{product}/extras', 'ProductExtrasController');

    /** Q */
    Route::resource('quotes', 'QuotesController');

    /** R */
    Route::get('reports/orders', 'ReportOrdersController@index');

    /** S */
    Route::resource('sessions', 'SessionController');

    /** Z */
    Route::resource('zones', 'ZonesController');



});


