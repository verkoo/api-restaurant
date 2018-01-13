<?php

namespace App\Listeners;


use Verkoo\Common\Events\TicketButtonPressed;
use Verkoo\Common\Factories\TicketFactory;

class PrintTicket
{
    /**
     * @var TicketFactory
     */
    private $ticketFactory;

    /**
     * Create the event listener.
     * @param TicketFactory $ticketFactory
     */
    public function __construct(TicketFactory $ticketFactory)
    {
        $this->ticketFactory = $ticketFactory;
    }

    /**
     * Handle the event.
     *
     * @param  TicketButtonPressed  $event
     * @return void
     */
    public function handle(TicketButtonPressed $event)
    {
        $this->ticketFactory->createProforma($event)->printTicket();
    }
}
