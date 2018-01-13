<?php

namespace App\Http\Controllers\Api;

use App\Entities\Line;
use Verkoo\Common\Entities\DefaultDeliveryNote;

class DefaultDeliveryNotesController extends ApiController
{
    public function index()
    {
        $deliveryNotes = DefaultDeliveryNote::searchByCustomer()->get();

        return $this->respond($deliveryNotes);
    }

    public function store()
    {
        $this->validate(request(), [
            'customer_id' => 'required',
        ]);

        if (DefaultDeliveryNote::where('customer_id', request('customer_id'))->exists()) {
           return $this->setStatusCode(422)->respondWithError('Albarán por defecto ya existe para este cliente');
        }

        $deliveryNote = DefaultDeliveryNote::create(request()->all());

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $deliveryNote->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function update(DefaultDeliveryNote $default_delivery_note)
    {
        $this->validate(request(), [
            'customer_id' => 'required',
        ]);

        $default_delivery_note->update(request()->all());

        $default_delivery_note->lines()->delete();

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $default_delivery_note->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function destroy(DefaultDeliveryNote $default_delivery_note)
    {
        $default_delivery_note->delete();

        return $this->respond(['success' => true]);
    }
}
