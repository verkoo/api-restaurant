<?php

namespace App\Http\Controllers\Api;

use App\Entities\Line;
use App\Filters\DocumentFilter;
use Verkoo\Common\Entities\Invoice;

class InvoicesController extends ApiController
{
    public function index()
    {
        $invoices = Invoice::searchBetweenDates()
            ->searchByCustomer()
            ->searchBySerie()
            ->get();

        $invoices = (new DocumentFilter(request('type'), $invoices))->apply();

        return $this->respond($invoices);
    }

    public function store()
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'customer_id' => 'required',
            'serie' => 'required|numeric',
        ]);

        $invoice = Invoice::create(request()->all());

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $invoice->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function update(Invoice $invoice)
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'customer_id' => 'required',
            'serie' => 'required|numeric',
            'cashed_amount' => 'numeric',
        ]);

        $invoice->update(request()->all());

        $invoice->lines()->delete();

        if ($lines = request('lines')) {
            foreach ($lines as $line) {
                if (!$line['product_id']) $line['product_id'] = null;
                $line = new Line($line);
                $invoice->lines()->save($line);
            }
        }

        return $this->respond(['success' => true]);
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();

        return $this->respond(['success' => true]);
    }
}
