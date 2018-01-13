<?php

namespace App\Events;

use App\Entities\Order;
use Illuminate\Queue\SerializesModels;

class OrderSentToKitchen extends Event
{
    use SerializesModels;
    
    public $order;
    
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
