<?php

namespace App\Providers;

use App\Events\OrderSentToKitchen;
use Verkoo\Common\Events\OrderCashed;
use Verkoo\Common\Events\SessionCreated;
use Verkoo\Common\Events\TicketButtonPressed;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderSentToKitchen::class => [
            'App\Listeners\PrintKitchenTicket',
        ],
        TicketButtonPressed::class => [
            'App\Listeners\PrintTicket',
        ],
        OrderCashed::class => [
            'App\Listeners\CreateDocumentFromOrder',
            'App\Listeners\PrintCashTicket',
        ],
        SessionCreated::class => [
            'App\Listeners\ResetStock',
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
