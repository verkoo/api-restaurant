<?php

use Carbon\Carbon;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Entities\DeliveryNote;
use Verkoo\Common\Entities\Invoice;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Payment;
use Verkoo\Common\Entities\Tax;
use Tests\TestCase;
use App\Entities\Menu;
use App\Entities\Line;
use App\Entities\Order;
use App\Entities\Table;
use App\Entities\MenuOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class OrderTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function an_order_has_menus()
    {
        $order = factory(Order::class)->create();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $order->menus);
    }

    /** @test */
    public function an_order_has_lines()
    {
        $order = factory(Order::class)->create();

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $order->lines);
    }

    /** @test */
    public function an_order_can_belong_to_a_table()
    {
        $table = factory(Table::class)->create();
        $order = factory(Order::class)->create(['table_id' => $table->id]);

        $this->assertInstanceOf(Table::class, $order->table);
    }

    /** @test */
    public function an_order_has_taxes_types()
    {
        $order = create(Order::class);

        $tax10 = create(Tax::class, ['percentage' => 10, 'name' => 'TAX10']);
        $tax20 = create(Tax::class, ['percentage' => 20, 'name' => 'TAX20']);

        $menu10 = create(Menu::class, ['tax_id' => $tax10->id]);

        create(Line::class, [
            'product_name' => 'PRODUCT WITH 10% TAX',
            'price' => '10,00',
            'vat' => 10,
            'lineable_id' => $order->id,
            'lineable_type' => $order->getMorphClass(),
        ]);

        create(Line::class, [
            'product_name' => 'PRODUCT WITH 10% TAX',
            'price' => '10,00',
            'vat' => 10,
            'lineable_id' => $order->id,
            'lineable_type' => $order->getMorphClass(),
        ]);

        create(Line::class, [
            'product_name' => 'PRODUCT WITH 20% TAX',
            'price' => '10,00',
            'vat' => 20,
            'quantity' => 2,
            'lineable_id' => $order->id,
            'lineable_type' => $order->getMorphClass(),
        ]);

        create(MenuOrder::class, [
            'name' => 'MENU WITH 10% TAX',
            'order_id' => $order->id,
            'menu_id' => $menu10->id,
            'price' => 1000,
        ]);

        $this->assertEquals($order->taxes, [
            '10' => [
                'base' => '27,27',
                'vat' => '2,73',
            ],
            '20' => [
                'base' => '16,67',
                'vat' => '3,33',
            ],
        ]);

    }

    /** @test */
    public function it_calculates_the_tax_for_a_vat_included_price_of_100()
    {
        $order = create(Order::class);

        $tax20 = create(Tax::class, ['percentage' => 21, 'name' => 'TAX21']);

        create(Line::class, [
            'product_name' => 'PRODUCT WITH 21% TAX',
            'price' => '100,00',
            'vat' => 21,
            'lineable_id' => $order->id,
            'lineable_type' => $order->getMorphClass(),
        ]);

        $this->assertEquals($order->taxes, [
            '21' => [
                'base' => '82,64',
                'vat' => '17,36',
            ],
        ]);
    }

    /** @test */
    public function it_returns_formatted_date()
    {
        $order = factory(Order::class)->create(['date' => '01/02/2013']);

        $this->assertEquals('01/02/2013', $order->date);
    }

    /** @test */
    public function it_returns_year_with_four_digits_when_passing_two()
    {
        $order = factory(Order::class)->create(['date' => '01/02/13']);

        $this->assertEquals('01/02/2013', $order->date);
    }

    /** @test */
    public function the_total_amount_is_the_sum_of_lines_and_menus()
    {
        $order = factory(Order::class)->create();
        $line1 = factory(Line::class)->make(['quantity' => 2, 'price' => 3]);
        $line2 = factory(Line::class)->make(['quantity' => 3, 'price' => 1]);
        $order->lines()->save($line1);
        $order->lines()->save($line2);

        $this->assertEquals(9, $order->total);

        create(MenuOrder::class,['order_id' => $order->id, 'price' => 1200]);

        $this->assertEquals(21, $order->fresh()->total);
    }


    /** @test */
    public function the_total_amount_is_the_sum_of_lines_and_menus_minus_discount_if_any()
    {
        $order = factory(Order::class)->create(['discount' => '7,20']);
        $line1 = factory(Line::class)->make(['quantity' => 2, 'price' => 3]);
        $line2 = factory(Line::class)->make(['quantity' => 3, 'price' => 1]);
        $order->lines()->save($line1);
        $order->lines()->save($line2);

        $this->assertEquals(1.80, $order->total);

        create(MenuOrder::class,['order_id' => $order->id, 'price' => 1200]);

        $this->assertEquals(13.8, $order->fresh()->total);
    }

    /** @test */
    public function the_total_cashed_amount_is_the_sum_of_lines_and_menus_from_the_orders_whit_cash_payment_id_in_settings()
    {
        $payment = create(Payment::class);
        create(Options::class, ['payment_id' => $payment->id]);

        $order = create(Order::class,['payment_id' => $payment->id]);
        $line1 = make(Line::class,['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class,['quantity' => 3, 'price' => 1]);
        $order->lines()->save($line1);
        $order->lines()->save($line2);
        create(MenuOrder::class,['order_id' => $order->id, 'price' => 1200]);

        $this->assertEquals(21, $order->fresh()->total_cashed);

        $order->payment_id = create(Payment::class)->id;
        $order->save();

        $this->assertEquals(0, $order->fresh()->total_cashed);
    }

    /** @test */
    public function it_returns_the_total_amount_of_cash_when_the_order_is_cashed_and_the_payment_method_is_the_one_in_settings()
    {
        $payment = factory(Payment::class)->create(["name" => 'Contado']);
        factory(Options::class)->create(['payment_id' => $payment->id]);

        $line1 = factory(Line::class)->make(['quantity' => 2, 'price' => 3]);
        $line2 = factory(Line::class)->make(['quantity' => 3, 'price' => 1]);
        $order = factory(Order::class)->create(['payment_id' => $payment->id]);
        $order->lines()->save($line1);
        $order->lines()->save($line2);

        $this->assertEquals(9, $order->total_cashed);
    }

    /** @test */
    public function it_returns_zero_in_total_cash_when_the_order_is_cashed_and_the_payment_method_is_not_the_one_in_settings()
    {
        $payment = factory(Payment::class)->create(["name" => 'Contado']);
        factory(Options::class)->create();

        $line1 = factory(Line::class)->make(['quantity' => 2, 'price' => 3]);
        $line2 = factory(Line::class)->make(['quantity' => 3, 'price' => 1]);
        $order = factory(Order::class)->create(['payment_id' => $payment->id]);
        $order->lines()->save($line1);
        $order->lines()->save($line2);

        $this->assertEquals(0, $order->total_cashed);
    }

    /** @test */
    public function it_returns_zero_in_total_cash_when_the_order_is_not_cashed()
    {
        $line1 = factory(Line::class)->make(['quantity' => 2, 'price' => 3]);
        $line2 = factory(Line::class)->make(['quantity' => 3, 'price' => 1]);
        $order = factory(Order::class)->create(['payment_id' => null]);
        $order->lines()->save($line1);
        $order->lines()->save($line2);

        $this->assertEquals(0, $order->total_cashed);
    }

    /** @test */
    public function it_applies_a_discount_when_showing_the_report_if_it_is_defined_in_env_file()
    {
        $order = create(Order::class);
        create(Line::class, [
            'lineable_id' => $order->id,
            'lineable_type' => $order->getMorphClass(),
            'quantity' => 1,
            'price' => 100,
        ]);

        //ENV VARIABLE DOESN'T EXISTS
        $this->assertEquals(100, $order->total);
        $this->assertEquals(100, $order->total_dto);

        //Loads test .env file
        //KEY DTO_REPORTS IS 20 BY DEFAULT IN TESTING
        if (file_exists(dirname(__DIR__) . '/.env.test')) {
            (new \Dotenv\Dotenv(dirname(__DIR__), '.env.test'))->load();
        }

        $this->assertEquals(100, $order->total);
        $this->assertEquals(80, $order->total_dto);
    }

    /** @test */
    public function an_order_is_cashed_if_payment_id_is_persisted_and_some_amount_is_passed()
    {
        $payment = create(Payment::class);
        $order = create(Order::class);

        $order->markAsCashed($payment->id, 100);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_id' => $payment->id,
        ]);
    }

    /** @test */
    public function cashed_amount_is_persisted_in_cents()
    {
        $order = create(Order::class);

        $order->cashed_amount = '10.23';
        $order->save();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'cashed_amount' => 1023,
        ]);
    }

    /** @test */
    public function an_order_copies_to_invoice_if_it_is_fully_cashed()
    {
        $payment = create(Payment::class);
        $customer = create(Customer::class);

        $order = create(Order::class, [
            'customer_id' => $customer->id,
            'serie' => 8,
        ]);
        create(Line::class,[
            'lineable_type' => $order->getMorphClass(),
            'lineable_id' => $order->id,
            'price' => 20
        ]);

        $order->markAsCashed($payment->id, 21);

        tap(Invoice::first(), function ($invoice) use ($customer, $order) {
            $this->assertInstanceOf(Invoice::class, $invoice);
            $this->assertEquals($customer->id, $invoice->customer->id);
            $this->assertEquals($order->serie, $invoice->serie);
            $this->assertEquals($order->date, $invoice->date);
            $this->assertEquals(21, $invoice->cashed_amount);
            $this->assertCount(1, $invoice->lines);
        });
    }

    /** @test */
    public function an_order_copies_to_delivery_note_if_it_is_not_fully_cashed()
    {
        $payment = create(Payment::class);
        $customer = create(Customer::class);

        $order = create(Order::class, [
            'customer_id' => $customer->id,
            'serie'       => 8,
        ]);

        create(Line::class,[
            'lineable_type' => $order->getMorphClass(),
            'lineable_id' => $order->id,
            'price' => 20
        ]);

        $order->markAsCashed($payment->id, 19);

        tap(DeliveryNote::first(), function ($deliveryNote) use ($customer, $order) {
            $this->assertInstanceOf(DeliveryNote::class, $deliveryNote);
            $this->assertEquals($order->serie, $deliveryNote->serie);
            $this->assertEquals($order->date, $deliveryNote->date);
            $this->assertEquals(19, $deliveryNote->cashed_amount);
            $this->assertEquals($customer->id, $deliveryNote->customer->id);
            $this->assertCount(1, $deliveryNote->lines);
        });
    }

    /** @test */
    public function an_order_does_not_copy_to_invoice_if_it_is_not_fully_cashed()
    {
        $payment = create(Payment::class);

        $order = create(Order::class);
        create(Line::class,[
            'lineable_type' => $order->getMorphClass(),
            'lineable_id' => $order->id,
            'price' => 20
        ]);

        $order->markAsCashed($payment->id, 19);

        $this->assertCount(0, Invoice::all());
    }

    /** @test */
    public function getting_the_invoice_is_false_when_order_has_not_been_billed()
    {
        $order = create(Order::class);

        $this->assertFalse($order->getInvoice());
    }

    /** @test */
    public function it_returns_the_related_invoice_when_order_has_been_billed()
    {
        $order = create(Order::class);
        $invoice = create(Invoice::class);

        create(Line::class, [
            'lineable_id' => $invoice->id,
            'lineable_type' => Invoice::class,
            'order_number' => $order->id,
        ]);

        tap($order->getInvoice(), function($orderInvoice) use ($invoice) {
            $this->assertInstanceOf(Invoice::class, $orderInvoice);
            $this->assertEquals($orderInvoice->id, $invoice->id);
        });
    }

    /** @test */
    public function getting_the_delivery_note_is_false_when_order_has_not_been_passed_to_delivery_note()
    {
        $order = create(Order::class);

        $this->assertFalse($order->getDeliveryNote());
    }

    /** @test */
    public function it_returns_the_related_delivery_note_when_order_has_been_passed_to_delivery_note()
    {
        $order = create(Order::class);
        $delivery = create(DeliveryNote::class);

        create(Line::class, [
            'lineable_id' => $delivery->id,
            'lineable_type' => DeliveryNote::class,
            'order_number' => $order->id,
        ]);

        tap($order->getDeliveryNote(), function($orderInvoice) use ($delivery) {
            $this->assertInstanceOf(DeliveryNote::class, $delivery);
            $this->assertEquals($orderInvoice->id, $delivery->id);
        });
    }

    /** @test */
    public function an_order_generates_an_autoincrement_number_based_on_it_serie_when_created()
    {
        Carbon::setTestNow(Carbon::create(date('Y'),'01', '01'));

        create(Order::class, ['serie' => 1]);
        create(Order::class, ['serie' => 1]);

        create(Order::class, ['serie' => 2]);

        $order1 = create(Order::class,['serie' => 1]);
        $order2 = create(Order::class,['serie' => 2]);

        $this->assertEquals(date('y') . '0003', $order1->fresh()->number);
        $this->assertEquals(date('y') . '0002', $order2->number);

        Carbon::setTestNow();
    }

    /** @test */
    public function it_stores_discount_in_cents()
    {
        $order = create(Order::class, [
            'discount' => '12,50'
        ]);

        $this->assertEquals(12.5, $order->discount);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
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
        $order = create(Order::class, [ 'discount' => '1,20' ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $order->lines()->save($line1);
        $order->lines()->save($line2);

        $this->assertEquals(960, $order->subtotalInCents);
    }

    /** @test */
    public function subtotal_is_formatted_subtotal()
    {
        $order = create(Order::class, [ 'discount' => '1,20' ]);

        $line1 = make(Line::class, ['quantity' => 2, 'price' => 3]);
        $line2 = make(Line::class, ['quantity' => 3, 'price' => 1.20]);
        $order->lines()->save($line1);
        $order->lines()->save($line2);

        $this->assertEquals('9,60', $order->subtotal);
    }
}
