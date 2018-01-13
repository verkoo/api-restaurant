<?php

namespace App\Http\Controllers\Api;

use App\Entities\Order;
use App\Entities\MenuOrder;
use Illuminate\Http\Request;

use App\Http\Requests;

class MenuOrdersController extends ApiController
{
    public function store(Order $order, Request $request) 
    {
        $menu = new MenuOrder($request->all());
        
        $menu = $order->menus()->save($menu);

        return $this->respond($menu->fresh());
    }

    public function destroy(Order $order, MenuOrder $menu)
    {
        $menu->delete();

        return $this->respond($order->menus);
    }
}
