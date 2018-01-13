<?php

namespace App\Http\Controllers\Api;

use App\Entities\Order;
use Verkoo\Common\Entities\Session;
use Illuminate\Support\Facades\Auth;

class SessionController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index() 
    {
        $boxes = Auth::user()->boxes()->withOpenSessions()->get();

        return $this->respond($boxes);
    }
    
    public function show($session)
    {
        $orders = Order::with('table')->notCashed()->whereSessionId($session)->get();
        return $this->respond($orders);
    }

    public function destroy(Session $session)
    {
        Order::whereSessionId($session->id)->notCashed()->delete();

        $session->load('orders');

        return $this->respond([
            "ok" => true,
            "session" => $session->toArray()
        ]);
    }
}
