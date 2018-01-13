<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;

use Illuminate\Support\ServiceProvider;

class ComposerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer([
            'lines.index',
            'lines.create',
            'lines.edit'
        ], 'App\Http\ViewComposers\LineComposer');

        View::composer([
            'quotes.create',
            'quotes.edit',
            'orders.create',
            'orders.edit',
            'delivery-notes.create',
            'delivery-notes.edit',
            'invoices.create',
            'invoices.edit'
        ], 'App\Http\ViewComposers\DocumentComposer');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
