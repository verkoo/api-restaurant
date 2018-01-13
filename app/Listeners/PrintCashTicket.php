<?php

namespace App\Listeners;

use Verkoo\Common\Entities\Settings;
use Verkoo\Common\Events\OrderCashed;
use Verkoo\Common\Factories\TicketFactory;

class PrintCashTicket
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
     * @param  OrderCashed  $event
     * @return void
     */
    public function handle(OrderCashed $event)
    {
        if (Settings::get('print_ticket_when_cash')) {
            $this->ticketFactory->createTicket($event)->printTicket();
        }

        if (Settings::get('open_drawer_when_cash')) {
            $this->ticketFactory->createOpenDrawerTicket()->printTicket();
        }
    }
}
