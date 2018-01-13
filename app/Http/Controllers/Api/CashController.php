<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Entities\Line;
use App\Entities\Order;
use Verkoo\Common\Events\TicketButtonPressed;
use Illuminate\Http\Request;
use Verkoo\Common\Entities\Settings;

class CashController extends ApiController
{
    public function show(Order $order) 
    {
        event(new TicketButtonPressed($order));
    }

    public function store()
    {
        $this->validate(request(), [
            'cashed_amount' => 'required',
        ]);

        $order = Order::create([
            'session_id' => request('session_id'),
            'serie' => Settings::get('default_tpv_serie'),
            'payment_id' => request('session_id'),
            'customer_id' => Settings::get('cash_customer'),
            'date'  => Carbon::now()->format('d/m/Y'),
            'user_id' => auth()->id(),
        ]);

        $line = new Line([
            'price' => request('cashed_amount'),
            'product_name' => 'VENTA CONTADO',
            'quantity' => 1,
        ]);

        $order->lines()->save($line);

        $order->markAsCashed(request('session_id'), request('cashed_amount'));
    }
    
    public function update(Order $order, Request $request)
    {
        $this->validate($request, [
            'payment_id' => 'required',
            'cashed_amount' => 'required',
        ]);
        
        $order->load('table');

        $order->markAsCashed($request->payment_id, $request->cashed_amount, $request->diners);
    }
}
