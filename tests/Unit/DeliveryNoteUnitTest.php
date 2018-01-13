<?php

use Carbon\Carbon;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\DeliveryNote;
use Verkoo\Common\Entities\Invoice;
use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DeliveryNoteUnitTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function lines_are_deleted_when_delivery_note_is_deleted()
    {
        $deliveryNote = create(DeliveryNote::class);

        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $this->assertDatabaseHas('lines',[
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $deliveryNote->fresh()->delete();

        $this->assertDatabaseMissing('lines',[
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
        ]);
    }

    /** @test */
    public function products_associated_with_lines_restore_their_stocks_when_delivery_note_is_deleted()
    {
        $deliveryNote = create(DeliveryNote::class);
        $product = create(Product::class, ['stock' => 2]);

        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'product_id'    => $product->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $this->assertEquals(1, $product->fresh()->stock);

        $deliveryNote->fresh()->delete();

        $this->assertEquals(2, $product->fresh()->stock);
    }

    /** @test */
    public function a_delivery_note_can_not_be_updated_if_it_was_billed()
    {
        $this->expectException(Exception::class);

        $invoice = create(Invoice::class);
        $customer = create(Customer::class);
        $deliveryNote = create(DeliveryNote::class, ['customer_id' => $customer->id]);
        create(Line::class,[
            'lineable_id' => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $invoice->copyLinesFromDeliveryNotes([
            $deliveryNote->id,
        ]);

        $this->assertEquals($customer->id, $deliveryNote->customer_id);

        $deliveryNote->fresh()->update(['customer_id' => create(Customer::class)->id]);

        $this->assertEquals($customer->id, $deliveryNote->fresh()->customer_id);
    }

    /** @test */
    public function a_delivery_note_can_not_be_deleted_if_it_was_billed()
    {
        $this->expectException(Exception::class);

        $invoice = create(Invoice::class);
        $customer = create(Customer::class);
        $deliveryNote = create(DeliveryNote::class, ['customer_id' => $customer->id]);
        create(Line::class,[
            'lineable_id' => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $invoice->copyLinesFromDeliveryNotes([
            $deliveryNote->id,
        ]);

        $this->assertEquals(1, DeliveryNote::count());

        $deliveryNote->fresh()->delete();

        $this->assertEquals(1, DeliveryNote::count());
    }

    /** @test */
    public function a_delivery_note_generates_an_autoincrement_number_based_on_it_serie_when_created()
    {
        Carbon::setTestNow(Carbon::create(date('Y'),'01', '01'));

        create(DeliveryNote::class, ['serie' => 1]);
        create(DeliveryNote::class, ['serie' => 1]);

        create(DeliveryNote::class, ['serie' => 2]);

        $delivery1 = create(DeliveryNote::class,['serie' => 1]);
        $delivery2 = create(DeliveryNote::class,['serie' => 2]);

        $this->assertEquals(date('y') . '0003', $delivery1->number);
        $this->assertEquals(date('y') . '0002', $delivery2->number);

        Carbon::setTestNow();
    }

    /** @test */
    public function a_delivery_note_knows_if_it_has_pending_amount()
    {
        $deliveryNote = create(DeliveryNote::class);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'quantity'      => 1,
            'price'         => 20,
        ]);

        $this->assertTrue($deliveryNote->fresh()->hasPendingAmount());
        $deliveryNote->cashed_amount = 20;
        $deliveryNote->save();
        $this->assertFalse($deliveryNote->fresh()->hasPendingAmount());
    }

    /** @test */
    public function a_delivery_note_knows_their_pending_amount()
    {
        $deliveryNote = create(DeliveryNote::class);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'quantity'      => 1,
            'price'         => 20,
        ]);

        $this->assertEquals(20, $deliveryNote->fresh()->getPendingAmount());
    }

    /** @test */
    public function a_delivery_can_cash_pending_amount()
    {
        $deliveryNote = create(DeliveryNote::class);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'quantity'      => 1,
            'price'         => 20,
        ]);

        $this->assertEquals(20, $deliveryNote->getPendingAmount());
        $deliveryNote->cash(10);
        $this->assertEquals(10, $deliveryNote->getPendingAmount());
    }

    /** @test */
    public function a_delivery_can_cash_full_pending_amount()
    {
        $deliveryNote = create(DeliveryNote::class);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'quantity'      => 1,
            'price'         => 20,
        ]);

        $this->assertEquals(20, $deliveryNote->fresh()->getPendingAmount());
        $deliveryNote->cash(30);
        $this->assertEquals(0, $deliveryNote->getPendingAmount());
    }

    /** @test */
    public function a_delivery_note_knows_the_invoice_number_if_it_is_billed()
    {
        $deliveryNote = create(DeliveryNote::class);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
            'customer_invoice_number'      => date('y') . '0012',
        ]);

        $this->assertEquals(date('y') . '0012', $deliveryNote->fresh()->invoiceNumber);
    }

    /** @test */
    public function a_delivery_note_gets_null_in_the_invoice_number_if_it_is_not_billed()
    {
        $deliveryNote = create(DeliveryNote::class);
        create(Line::class, [
            'lineable_id'   => $deliveryNote->id,
            'lineable_type' => DeliveryNote::class,
        ]);

        $this->assertNull($deliveryNote->invoiceNumber);
    }

    /** @test */
    public function it_stores_discount_in_cents()
    {
        $delivery = create(DeliveryNote::class, [
            'discount' => '12,50'
        ]);

        $this->assertEquals(12.5, $delivery->discount);
        $this->assertDatabaseHas('delivery_notes', [
            'id' => $delivery->id,
            'discount' => 1250
        ]);
    }

    /** @test */
    public function total_amount_in_cents_is_the_sum_of_the_lines_minus_discount()
    {
        $delivery = create(DeliveryNote::class, [
            'discount' => '1,20'
        ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $delivery->lines()->save($line1);
        $delivery->lines()->save($line2);

        $this->assertEquals(840, $delivery->total_in_cents);
    }

    /** @test */
    public function subtotalInCents_is_the_sum_of_the_lines_without_discount()
    {
        $delivery = create(DeliveryNote::class, [ 'discount' => '1,20' ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $delivery->lines()->save($line1);
        $delivery->lines()->save($line2);

        $this->assertEquals(960, $delivery->subtotalInCents);
    }

    /** @test */
    public function subtotal_is_formatted_subtotal()
    {
        $delivery = create(DeliveryNote::class, [ 'discount' => '1,20' ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $delivery->lines()->save($line1);
        $delivery->lines()->save($line2);

        $this->assertEquals('9,60', $delivery->subtotal);
    }
}