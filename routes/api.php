<?php

Route::group(['middleware' => 'auth:api'], function () {
    Route::resource('lines', 'LinesController');
    Route::resource('orders-tpv', 'OrdersTpvController');
    Route::resource('sessions', 'SessionController');
    Route::get('products', 'ProductsController@index');
    Route::post('products', 'ProductsController@updateStock');
    Route::resource('delivery-notes', 'DeliveryNotesController');
    Route::resource('default-delivery-notes', 'DefaultDeliveryNotesController');
    Route::resource('products/{product}/allergens', 'ProductAllergensController');
    Route::resource('products/{product}/customers', 'ProductCustomersController');
    Route::resource('expediture-delivery-notes', 'ExpeditureDeliveryNotesController');
    Route::post('delivery-notes/invoices', 'DeliveryNotesToInvoiceController@store');
    Route::resource('invoices', 'InvoicesController');
    Route::resource('quotes', 'QuotesController');
    Route::resource('orders', 'OrdersController');
    Route::get('reports/orders', 'ReportOrdersController@index');
    Route::get('categories/{category}/products', 'CategoryProductsController@index');
    Route::resource('cash', 'CashController', ['parameters' => ['cash' => 'order']]);
});


