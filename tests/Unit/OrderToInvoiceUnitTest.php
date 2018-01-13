<?php

use Verkoo\Common\Entities\Invoice;
use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Order;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderToInvoiceUnitTest extends TestCase
{
    use DatabaseTransactions;
    
    /** @test */
    public function an_invoice_copy_lines_from_orders()
    {
        $invoice = create(Invoice::class);
        $order = create(Order::class);

        create(Line::class, [
            'lineable_id'   => $order->id,
            'lineable_type' => $order->getMorphClass(),
        ]);

        $invoice->copyLinesFromOrder($order);

        $this->assertCount(1, $invoice->lines);
    }

    /** @test */

    public function if_order_has_no_lines_new_invoice_has_no_lines_too()
    {
        $invoice = create(Invoice::class);
        $order = create(Order::class);

        $invoice->copyLinesFromOrder($order);

        $this->assertCount(0, $invoice->lines);
    }

    /** @test */
    public function exception_is_thrown_if_order_does_not_exist()
    {
        $this->expectException(TypeError::class);
        $invoice = create(Invoice::class);

        $invoice->copyLinesFromOrder(99);
    }

    /** @test */
    public function lines_from_order_save_the_invoice_id()
    {
        $invoice = create(Invoice::class);
        $order = create(Order::class);

        $line = create(Line::class, [
            'lineable_id'   => $order->id,
            'lineable_type' => $order->getMorphClass(),
        ]);

        $invoice->copyLinesFromOrder($order);

        $this->assertEquals($invoice->id, $line->fresh()->customer_invoice_number);
    }

    /** @test */
    public function new_invoice_lines_store_the_order_id()
    {
        $invoice = create(Invoice::class);
        $order = create(Order::class);

        create(Line::class, [
            'lineable_id'   => $order->id,
            'lineable_type' => $order->getMorphClass(),
        ]);

        $invoice->copyLinesFromOrder($order);

        $this->assertEquals($order->id, $invoice->lines->first()->order_number);
    }

    /** @test */
    public function related_products_in_invoice_lines_keeps_their_stock()
    {
        $invoice = create(Invoice::class);
        $order = create(Order::class);
        $product = create(Product::class, ['stock' => 5]);

        create(Line::class, [
            'quantity'   => 1,
            'product_id'   => $product->id,
            'lineable_id'   => $order->id,
            'lineable_type' => Order::class,
        ]);

        $this->assertEquals(4, $product->fresh()->stock);

        $invoice->copyLinesFromOrder($order);

        $this->assertEquals(4, $product->fresh()->stock);
    }
}