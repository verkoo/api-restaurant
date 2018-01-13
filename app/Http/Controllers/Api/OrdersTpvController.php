<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Entities\Table;
use App\Entities\Order;
use Verkoo\Common\Entities\Settings;
use Illuminate\Http\Request;

class OrdersTpvController extends ApiController
{
    public function index()
    {
        $orders = Order::notCashed()->get();
        return $this->respond($orders);
    }

    public function store()
    {
        if (Table::isOccupied(request('table_id'))) {
            return $this->respondWithError("Esta mesa ya tiene un pedido abierto");
        }

        $order = Order::create([
            'session_id' => request('session_id'),
            'cashed' => false,
            'table_id' => request('table_id'),
            'payment_id' => null,
            'serie' => Settings::get('default_tpv_serie'),
            'customer_id' => Settings::get('cash_customer'),
            'date'  => Carbon::now()->format('d/m/Y'),
            'user_id' => auth()->id(),
        ]);
        $order->load('table');

        return $this->respond($order);
    }

    public function update(Request $request, Order $orders_tpv)
    {
        if($request->has('table_id')) {
            $orders_tpv->table_id = $request->table_id;
        }

        if($request->has('customer_id')) {
            $orders_tpv->customer_id = $request->customer_id;
        }

        $orders_tpv->save();
        $orders_tpv->load('table');
        return $this->respond($orders_tpv);
    }
    
    public function show(Order $orders_tpv)
    {
        return $this->respond($orders_tpv);
    }

    public function destroy(Order $orders_tpv) {
        $orders_tpv->delete();
        return $this->respond(['success' => true]);
    }
}
