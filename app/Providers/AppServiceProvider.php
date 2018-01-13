<?php

namespace App\Providers;

use App\Contracts\CalendarInterface;
use App\Services\GoogleCalendar;
use Verkoo\Common\Entities\DeliveryNote;
use Validator;
use Laravel\Dusk\DuskServiceProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \Illuminate\Pagination\LengthAwarePaginator::defaultView('partials.pagination');

        //Validador para solo permitir editar a true el default de address
        Validator::extend('shouldBeTrue', function($attribute, $value, $parameters, $validator) {
            return !! $value;
        });
        Validator::extend('hasLines', function($attribute, $value, $parameters, $validator) {
            foreach ($value as $id) {
                $document = DeliveryNote::findOrFail($id);
                if ($document->lines->isEmpty()) return false;
            }
            return true;
        });

        Validator::extend('notBilled', function($attribute, $value, $parameters, $validator) {
            foreach ($value as $id) {
                $document = DeliveryNote::findOrFail($id);
                if ($document->hasBeenBilled()) return false;
            }
            return true;
        });

//        $this->app->bind(CalendarInterface::class, GoogleCalendar::class);

//        \DB::listen(function ($query) { \Log::info($query->sql, $query->bindings); });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local', 'testing')) {
            $this->app->register(DuskServiceProvider::class);
        }

        if($this->app->environment('testing')) {
            ini_set('memory_limit', '2G');
        }
    }
}
