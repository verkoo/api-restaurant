<?php

namespace App\Http\Controllers\Api;

use App\Entities\Line;
use App\Filters\DocumentFilter;
use Verkoo\Common\Entities\DeliveryNote;

class DeliveryNotesController extends ApiController
{
    public function index()
    {
        $deliveryNotes = DeliveryNote::searchBetweenDates()
            ->searchByCustomer()
            ->searchBySerie()
            ->get();

        $deliveryNotes = (new DocumentFilter(request('type'), $deliveryNotes))->apply();

        return $this->respond($deliveryNotes);
    }

    public function store()
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'customer_id' => 'required',
            'serie' => 'required|numeric',
        ]);

        $deliveryNote = DeliveryNote::create(request()->all());

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $deliveryNote->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function update(DeliveryNote $delivery_note)
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'customer_id' => 'required',
            'serie' => 'required|numeric',
            'cashed_amount' => 'numeric',
        ]);

        $delivery_note->update(request()->all());

        $delivery_note->lines()->delete();

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $delivery_note->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function destroy(DeliveryNote $delivery_note)
    {
        $delivery_note->delete();

        return $this->respond(['success' => true]);
    }
}
