<?php

namespace App\Listeners;

use App\Events\OrderSentToKitchen;
use Illuminate\Contracts\Queue\ShouldQueue;
use Verkoo\Common\Factories\TicketFactory;

class PrintKitchenTicket implements ShouldQueue
{
    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * Create the event listener.
     *
     * @param TicketFactory $ticketFactory
     */
    public function __construct(TicketFactory $ticketFactory)
    {
        $this->ticketFactory = $ticketFactory;
    }

    /**
     * Handle the event.
     *
     * @param  OrderSentToKitchen  $event
     * @return void
     */
    public function handle(OrderSentToKitchen $event)
    {
        $table = $event->order->table ? $event->order->table->name : 'SIN MESA';

        $lines = $event->order->notOrderedLines();
        $menuLines =$event->order->notOrderedProducts();

        $kitchens = $lines->merge($menuLines)->groupBy('kitchen_id');

        foreach ($kitchens as $kitchen) {
            if ($kitchen->count()){
                $this->ticketFactory->createKitchenTicket($kitchen, $table)->printTicket();
            }
        }
    }
}
