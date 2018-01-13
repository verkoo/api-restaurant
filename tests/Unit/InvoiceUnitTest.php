<?php

use Carbon\Carbon;
use Verkoo\Common\Entities\Invoice;
use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class InvoiceUnitTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function lines_are_deleted_when_invoice_is_deleted()
    {
        $invoice = create(Invoice::class);

        create(Line::class, [
            'lineable_id'   => $invoice->id,
            'lineable_type' => Invoice::class,
        ]);

        $this->assertDatabaseHas('lines',[
            'lineable_id'   => $invoice->id,
            'lineable_type' => Invoice::class,
        ]);

        $invoice->delete();

        $this->assertDatabaseMissing('lines',[
            'lineable_id'   => $invoice->id,
            'lineable_type' => Invoice::class,
        ]);
    }

    /** @test */
    public function products_associated_with_lines_restore_their_stocks_when_invoice_is_deleted()
    {
        $invoice = create(Invoice::class);
        $product = create(Product::class, ['stock' => 2]);

        create(Line::class, [
            'lineable_id'   => $invoice->id,
            'product_id'    => $product->id,
            'lineable_type' => Invoice::class,
        ]);

        $this->assertEquals(1, $product->fresh()->stock);

        $invoice->delete();

        $this->assertEquals(2, $product->fresh()->stock);
    }

    /** @test */
    public function an_invoice_generates_an_autoincrement_number_based_on_it_serie_when_created()
    {
        Carbon::setTestNow(Carbon::create(date('Y'),'01', '01'));

        create(Invoice::class, ['serie' => 1]);
        create(Invoice::class, ['serie' => 1]);

        create(Invoice::class, ['serie' => 2]);

        $invoice1 = create(Invoice::class,['serie' => 1]);
        $invoice2 = create(Invoice::class,['serie' => 2]);

        $this->assertEquals(date('y') . '0003', $invoice1->number);
        $this->assertEquals(date('y') . '0002', $invoice2->number);

        Carbon::setTestNow();
    }

    /** @test */
    public function it_stores_discount_in_cents()
    {
        $invoice = create(Invoice::class, [
            'discount' => '12,50'
        ]);

        $this->assertEquals(12.5, $invoice->discount);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'discount' => 1250
        ]);
    }

    /** @test */
    public function total_amount_in_cents_is_the_sum_of_the_lines_minus_discount()
    {
        $invoice = create(Invoice::class, [
            'discount' => '1,20'
        ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $invoice->lines()->save($line1);
        $invoice->lines()->save($line2);

        $this->assertEquals(840, $invoice->total_in_cents);
    }

    /** @test */
    public function subtotalInCents_is_the_sum_of_the_lines_without_discount()
    {
        $invoice = create(Invoice::class, [ 'discount' => '1,20' ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $invoice->lines()->save($line1);
        $invoice->lines()->save($line2);

        $this->assertEquals(960, $invoice->subtotalInCents);
    }

    /** @test */
    public function subtotal_is_formatted_subtotal()
    {
        $invoice = create(Invoice::class, [ 'discount' => '1,20' ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $invoice->lines()->save($line1);
        $invoice->lines()->save($line2);

        $this->assertEquals('9,60', $invoice->subtotal);
    }
}