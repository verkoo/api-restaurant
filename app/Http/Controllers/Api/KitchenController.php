<?php

namespace App\Http\Controllers\Api;

use App\Entities\Order;
use App\Events\OrderSentToKitchen;

class KitchenController extends ApiController
{
    public function update(Order $order)
    {
        if (request()->exists('discard')) {
            $order->discardNotServedLines();
        }
        elseif (!request()->exists('served')) {
            event(new OrderSentToKitchen($order));
        }

        $order->markNotServedLinesAsServed();

        return $this->respond(['ok' => true]);
    }
}