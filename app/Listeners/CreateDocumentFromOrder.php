<?php

namespace App\Listeners;

use Verkoo\Common\Entities\DeliveryNote;
use Verkoo\Common\Entities\Invoice;
use Verkoo\Common\Events\OrderCashed;

class CreateDocumentFromOrder
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OrderCashed  $event
     * @return void
     */
    public function handle(OrderCashed $event)
    {
        $class = DeliveryNote::class;

        if ($event->order->cashed_amount >= $event->order->total) {
            $class = Invoice::class;
        }

        $document = $class::create([
            'customer_id' => $event->order->customer_id,
            'serie' => $event->order->serie,
            'date' => $event->order->date,
            'cashed_amount' => $event->order->cashed_amount,
        ]);

        $document->copyLinesFromOrder($event->order);
    }
}
