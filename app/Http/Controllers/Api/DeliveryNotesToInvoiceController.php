<?php

namespace App\Http\Controllers\Api;

use Verkoo\Common\Entities\Invoice;

class DeliveryNotesToInvoiceController extends ApiController
{

    public function store()
    {
        $this->validate(request(), [
            'date' => 'required|date_format:d/m/Y',
            'customer_id' => 'required',
            'delivery_notes' => 'required|hasLines|notBilled',
        ]);

        $invoice = Invoice::create(request()->all());

        if ($deliveryNotes = request('delivery_notes')) {
            $invoice->copyLinesFromDeliveryNotes($deliveryNotes);
        }

        return $this->respond(['success' => true]);
    }
}
