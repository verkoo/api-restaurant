<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Entities\Order;

class ReportOrdersController extends ApiController
{
    public function index()
    {
        $this->validate(request(), [
            'date_from' => 'required|date_format:Y-m-d',
            'date_to' => 'required|date_format:Y-m-d',
        ]);

        return Order::whereDate('date', '>=',  request('date_from'))
            ->whereDate('date', '<=',  request('date_to'))
            ->get();
    }
}
