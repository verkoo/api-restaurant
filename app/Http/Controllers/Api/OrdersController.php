<?php

namespace App\Http\Controllers\Api;

use App\Entities\Line;
use App\Entities\Order;
use App\Filters\DocumentFilter;
use Verkoo\Common\Contracts\CalendarInterface;
use Verkoo\Common\Entities\Customer;

class OrdersController extends ApiController
{
    private $calendar;

    public function __construct(CalendarInterface $calendar)
    {
        $this->calendar = $calendar;
    }
    public function index()
    {
        $orders = Order::searchBetweenDates()
            ->searchByCustomer()
            ->searchBySerie()
            ->get();

        $orders = (new DocumentFilter(request('type'), $orders))->apply();

        return $this->respond($orders);
    }

    public function store()
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'customer_id' => 'required',
            'serie' => 'required|numeric',
        ]);

        $order = new Order(request()->all());
        $order->user_id = auth()->id();
        $order->save();

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $order->lines()->save($line);
            }
        }

        if (request('calendar_event')) {
            $customer = Customer::findOrFail(request('customer_id'));

            $this->calendar->store([
                'start' => request('date'),
                'end' => request('date'),
                'title' => sprintf("%s - %s", $order->number, $customer->name),
            ]);
        }

        return $this->respond(['success' => true]);
    }

    public function update(Order $order)
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'customer_id' => 'required',
            'serie' => 'required|numeric',
            'cashed_amount' => 'numeric',
        ]);

        $order->update(request()->all());

        $order->lines()->delete();

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $order->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return $this->respond(['success' => true]);
    }
}