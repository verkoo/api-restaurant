<?php

use App\Entities\Product;
use Verkoo\Common\Entities\DeliveryNote;
use Verkoo\Common\Entities\Invoice;
use Tests\TestCase;
use App\Entities\Line;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeliveryNoteToInvoiceUnitTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function an_invoice_copy_lines_from_delivery_notes()
    {
        $invoice = create(Invoice::class);
        $delivery1 = create(DeliveryNote::class);
        $delivery2 = create(DeliveryNote::class);

        create(Line::class, [
            'lineable_id'   => $delivery1->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        create(Line::class, [
            'lineable_id'   => $delivery2->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $invoice->copyLinesFromDeliveryNotes([
           $delivery1->id,
           $delivery2->id,
        ]);

        $this->assertCount(2, $invoice->lines);
    }

    /** @test */

    public function if_delivery_note_has_no_lines_new_invoice_has_no_lines_too()
    {
        $invoice = create(Invoice::class);
        $delivery1 = create(DeliveryNote::class);

        $invoice->copyLinesFromDeliveryNotes([
            $delivery1->id,
        ]);

        $this->assertCount(0, $invoice->lines);
    }

    /** @test */
    public function exception_is_thrown_if_delivery_note_does_not_exist()
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $invoice = create(Invoice::class);

        $invoice->copyLinesFromDeliveryNotes([99]);
    }

    /** @test */
    public function lines_from_delivery_note_save_the_invoice_number_with_serie_as_prefix()
    {
        $invoice = create(Invoice::class, [
            'serie' => 1
        ]);
        $delivery1 = create(DeliveryNote::class);

        $line = create(Line::class, [
            'lineable_id'   => $delivery1->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $invoice->copyLinesFromDeliveryNotes([
           $delivery1->id,
        ]);

        $this->assertEquals("1-{$invoice->number}", $line->fresh()->customer_invoice_number);
    }

    /** @test */
    public function new_invoice_lines_store_the_delivery_note_number_with_serie_as_prefix()
    {
        $invoice = create(Invoice::class);
        $delivery1 = create(DeliveryNote::class, [
            'serie' => 1
        ]);

        create(Line::class, [
            'lineable_id'   => $delivery1->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $invoice->copyLinesFromDeliveryNotes([
           $delivery1->id,
        ]);

        $this->assertEquals("1-{$delivery1->number}", $invoice->lines->first()->customer_delivery_note_number);
    }

    /** @test */
    public function related_products_in_invoice_lines_keeps_their_stock()
    {
        $invoice = create(Invoice::class);
        $delivery1 = create(DeliveryNote::class);
        $product = create(Product::class, ['stock' => 5]);

        create(Line::class, [
            'quantity'   => 1,
            'product_id'   => $product->id,
            'lineable_id'   => $delivery1->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $this->assertEquals(4, $product->fresh()->stock);

        $invoice->copyLinesFromDeliveryNotes([
           $delivery1->id,
        ]);

        $this->assertEquals(4, $product->fresh()->stock);
    }

    /** @test */
    public function delivery_note_is_fully_cashed_when_pass_to_invoice()
    {
        $invoice = create(Invoice::class);
        $delivery = create(DeliveryNote::class);

        create(Line::class, [
            'quantity'   => 1,
            'price'   => 20,
            'lineable_id'   => $delivery->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $this->assertEquals(20, $delivery->fresh()->getPendingAmount());

        $invoice->copyLinesFromDeliveryNotes([$delivery->id]);

        $this->assertEquals(0, $delivery->fresh()->getPendingAmount());
    }
}