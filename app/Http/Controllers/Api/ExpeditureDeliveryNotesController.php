<?php

namespace App\Http\Controllers\Api;

use App\Entities\Line;
use Verkoo\Common\Entities\ExpeditureDeliveryNote;

class ExpeditureDeliveryNotesController extends ApiController
{
    public function index()
    {
        $deliveryNotes = ExpeditureDeliveryNote::searchBetweenDates()
            ->searchBySupplier()
            ->searchBySerie()
            ->get();

        return $this->respond($deliveryNotes);
    }

    public function store()
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'supplier_id' => 'required',
        ]);

        $deliveryNote = ExpeditureDeliveryNote::create(request()->all());

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $deliveryNote->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function update(ExpeditureDeliveryNote $expediture_delivery_note)
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'supplier_id' => 'required',
        ]);

        $expediture_delivery_note->update(request()->all());

        $expediture_delivery_note->lines()->delete();

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $expediture_delivery_note->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function destroy(ExpeditureDeliveryNote $expediture_delivery_note)
    {
        $expediture_delivery_note->delete();

        return $this->respond(['success' => true]);
    }
}
