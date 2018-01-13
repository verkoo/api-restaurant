<?php

use Verkoo\Common\Events\OrderCashed;
use Verkoo\Common\Events\TicketButtonPressed;
use Tests\TestCase;
use App\Entities\Order;
use App\Entities\Table;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Payment;
use Verkoo\Common\Entities\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiCashTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = create(User::class);
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_marks_an_order_as_cashed()
    {
        $this->disableExceptionHandling();
        $payment = create(Payment::class);
        $order = create(Order::class);

        $response = $this->json('PUT',"api/cash/{$order->id}", [
            'payment_id' => $payment->id,
            'cashed_amount' => 1
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id'            => $order->id,
            'payment_id'       => $payment->id
        ]);
    }

    /** @test */
    public function it_sets_to_false_the_related_table()
    {
        $payment = factory(Payment::class)->create();
        factory(Options::class)->create();
        $table = factory(Table::class)->create();
        $order = factory(Order::class)->create(['table_id' => $table->id]);

        $response = $this->put("api/cash/{$order->id}", [
            'payment_id' => $payment->id,
            'cashed_amount' => 1
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'id'       => $order->id,
            'payment_id'   => $payment->id,
            'table_id' => null
        ]);
    }


    /** @test */
    public function payment_id_is_required_when_cashing_an_order()
    {
        $order = factory(Order::class)->create();

        $response = $this->json('PUT',"api/cash/{$order->id}",[
            'cashed_amount' => 100
        ]);

        $this->assertValidationErrors($response, 'payment_id');
    }

    /** @test */
    public function amount_is_required_when_cashing_an_order()
    {
        $payment = create(Payment::class);
        $order = create(Order::class);

        $response = $this->json('PUT',"api/cash/{$order->id}", [
            'payment_id' => $payment->id,
        ]);

        $this->assertValidationErrors($response, 'cashed_amount');
    }

    /** @test */
    public function it_fires_an_event_when_cash_an_order()
    {
        $this->expectsEvents(OrderCashed::class);

        $payment = factory(Payment::class)->create();
        $order = factory(Order::class)->create();

        $this->put("api/cash/{$order->id}", [
            'payment_id' => $payment->id,
            'cashed_amount' => 1
        ]);
    }

    /** @test */
    public function it_handles_a_listener_when_the_cash_event_is_fired()
    {
        $listener = Mockery::spy(\App\Listeners\PrintCashTicket::class);
        app()->instance(\App\Listeners\PrintCashTicket::class, $listener);

        $payment = factory(Payment::class)->create();
        $order = factory(Order::class)->create();
        $this->put("api/cash/{$order->id}", [
            'payment_id' => $payment->id,
            'cashed_amount' => 1
        ]);

        $listener->shouldHaveReceived('handle')->once();
    }

    /** @test */
    public function it_fires_an_event_when_print_the_ticket()
    {
        $this->expectsEvents(TicketButtonPressed::class);
        $order = factory(Order::class)->create();

        $this->get("api/cash/{$order->id}");
    }

    /** @test */
    public function it_handles_a_listener_when_the_ticket_event_is_fired()
    {
        $listener = Mockery::spy(\App\Listeners\PrintTicket::class);
        app()->instance(\App\Listeners\PrintTicket::class, $listener);

        $order = factory(Order::class)->create();
        $this->get("api/cash/{$order->id}");

        $listener->shouldHaveReceived('handle')->once();
    }

    /** @test */
    public function it_stores_an_order_directly_from_cash()
    {
        create(Options::class, [
           'default_tpv_serie' => 1,
           'cash_customer' => create(Customer::class)->id,
        ]);

        $this->post('api/cash', [
            'cashed_amount' => 10
        ]);

        tap(Order::first(), function($order) {
            $this->assertInstanceOf(Order::class, $order);
            $this->assertCount(1, $order->lines);
            $this->assertEquals("10,00", $order->lines->first()->price);
            $this->assertEquals("VENTA CONTADO", $order->lines->first()->product_name);
            $this->assertEquals(10, $order->total);
        });
    }

    /** @test */
    public function cashed_amount_is_required_creating_an_order_directly_from_cash()
    {
        $response = $this->json('POST', 'api/cash');

        $this->assertValidationErrors($response, 'cashed_amount');
    }
}