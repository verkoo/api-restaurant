<?php

use Tests\TestCase;
use App\Entities\Line;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Invoice;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\DeliveryNote;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiDeliveryNoteToInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User;
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_creates_a_new_invoice_with_lines_from_two_delivery_notes()
    {
        $delivery1 = create(DeliveryNote::class);
        $delivery2 = create(DeliveryNote::class);
        $customer = create(Customer::class);

        create(Line::class, [
            'lineable_id'   => $delivery1->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        create(Line::class, [
            'lineable_id'   => $delivery2->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $this->json('POST',"api/delivery-notes/invoices", [
            'date' => '01/01/2017',
            'customer_id' => $customer->id,
            'delivery_notes' => [
                $delivery1->id,
                $delivery2->id,
            ],
        ])->assertStatus(200);

        tap(Invoice::first(), function ($invoice) use ($customer) {
            $this->assertInstanceOf(Invoice::class, $invoice);
            $this->assertEquals($customer->id, $invoice->customer->id);
            $this->assertEquals('01/01/2017', $invoice->date);
            $this->assertCount(2, $invoice->lines);
        });
    }

    /** @test */
    public function at_least_one_delivery_note_id_is_required_when_creating_an_invoice_from_delivery_notes()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/delivery-notes/invoices", [
            'customer_id' => $customer->id,
            'date' => '01/01/2012',
            'delivery_notes' =>[],
        ]);
        $this->assertValidationErrors($response, 'delivery_notes');
    }

    /** @test */
    public function at_least_one_line_is_required_in_delivery_note_when_creating_an_invoice_from_delivery_notes()
    {
        $customer = create(Customer::class);
        $delivery1 = create(DeliveryNote::class);

        $response = $this->json('POST', "api/delivery-notes/invoices", [
            'customer_id' => $customer->id,
            'date' => '01/01/2010',
            'delivery_notes' => [ $delivery1->id ]
        ]);
        $this->assertValidationErrors($response, 'delivery_notes');
    }

    /** @test */
    public function delivery_note_must_not_be_billed_yet_when_creating_an_invoice_from_delivery_notes()
    {
        $customer = create(Customer::class);
        $delivery1 = create(DeliveryNote::class);
        create(Line::class, [
            'lineable_id'   => $delivery1->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $invoice = create(Invoice::class);
        $invoice->copyLinesFromDeliveryNotes([$delivery1->id]);

        $response = $this->json('POST', "api/delivery-notes/invoices", [
            'customer_id' => $customer->id,
            'date' => '01/01/2010',
            'delivery_notes' => [ $delivery1->id ]
        ]);
        $this->assertValidationErrors($response, 'delivery_notes');
    }

    /** @test */
    public function date_is_required_when_creating_an_invoice_from_delivery_notes()
    {
        $customer = create(Customer::class);
        $delivery1 = create(DeliveryNote::class);
        $response = $this->json('POST', "api/delivery-notes/invoices", [
            'customer_id' => $customer->id,
            'delivery_notes' => [ $delivery1->id ]
        ]);
        $this->assertValidationErrors($response, 'date');
    }


    /** @test */
    public function date_must_be_valid_when_creating_an_invoice_from_delivery_notes()
    {
        $customer = create(Customer::class);
        $delivery1 = create(DeliveryNote::class);
        $response = $this->json('POST', "api/delivery-notes/invoices", [
            'customer_id' => $customer->id,
            'date' => 'NOT VALID DATE',
            'delivery_notes' => [ $delivery1->id ]
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function customer_is_required_when_creating_an_invoice_from_delivery_notes()
    {
        $delivery1 = create(DeliveryNote::class);
        $response = $this->json('POST', "api/delivery-notes/invoices", [
            'date' => '01/01/2012',
            'delivery_notes' => [ $delivery1->id ]
        ]);
        $this->assertValidationErrors($response, 'customer_id');
    }
}