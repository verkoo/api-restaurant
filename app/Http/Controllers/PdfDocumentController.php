<?php

namespace App\Http\Controllers;

use App\Entities\Order;
use App\Entities\Quote;
use App\Entities\Invoice;
use App\Entities\DeliveryNote;

class PdfDocumentController extends Controller
{
    private $types = [
      'invoices' => [
          'class' => Invoice::class,
          'typeName' => 'Factura',
      ],
      'delivery-notes' => [
          'class' => DeliveryNote::class,
          'typeName' => 'Albarán',
      ],
      'orders' => [
          'class' => Order::class,
          'typeName' => 'Pedido',
      ],
      'quotes' => [
          'class' => Quote::class,
          'typeName' => 'Presupuesto',
      ]
    ];

    public function index($type, $id)
    {
        $class = $this->types[$type]['class'];
        $typeName = $this->types[$type]['typeName'];

        $document = $class::findOrFail($id);

        $lines = $document->lines->groupBy('customer_delivery_note_number');

        $allergenIcons = $document->allergenIcons;

        $pdf = \PDF::loadView('pdf.document', compact('document', 'lines', 'typeName', 'allergenIcons'));

        return $pdf->inline("{$type}_{$id}.pdf");
    }
}
