<?php

use Carbon\Carbon;
use Verkoo\Common\Contracts\CalendarInterface;
use Verkoo\Common\Entities\Customer;
use Verkoo\Common\Services\GoogleCalendar;
use Tests\TestCase;
use App\Entities\Line;
use Verkoo\Common\Entities\User;
use App\Entities\Order;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiOrdersTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = create(User::class);
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_orders()
    {
        $this->disableExceptionHandling();
        $customer = create(Customer::class, [
            'name' => 'John Doe',
        ]);

        $order = create(Order::class, [
            'date'        => '20/10/2016',
            'customer_id' => $customer->id,
            'cashed_amount' => 10.27,
            'discount' => '10.21',
        ]);

        create(Line::class, [
           'lineable_id'   => $order->id,
           'lineable_type' => $order->getMorphClass(),
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->get("api/orders");

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'date'     => '20/10/2016',
            'customer_name' => 'John Doe',
            'total'    => '25,02',
            'cashed_amount' => 10.27,
            'discount' => 10.21,
        ]);
    }

    /** @test */
    public function it_creates_a_new_order()
    {
        $calendarSpy = Mockery::spy(GoogleCalendar::class);
        $this->app->instance(CalendarInterface::class, $calendarSpy);

        $customer = create(Customer::class);

        $response = $this->post("api/orders", [
            'date'        => '20/10/2016',
            'serie'       => 1,
            'discount' => '10.21',
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'date'        => '2016-10-20',
            'serie'       => 1,
            'discount'    => 1021,
            'customer_id' => $customer->id,
        ]);

        $calendarSpy->shouldNotHaveReceived('store');
    }

    /** @test */
    public function it_may_create_a_calendar_event_when_creating_an_order()
    {
        $calendarSpy = Mockery::spy(GoogleCalendar::class);
        $this->app->instance(CalendarInterface::class, $calendarSpy);

        $customer = create(Customer::class, ['name' => 'FAKE CUSTOMER']);

        $response = $this->post("api/orders", [
            'date'        => '20/10/2016',
            'serie'       => 1,
            'discount' => '10.21',
            'calendar_event' => '1',
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'date'        => '2016-10-20',
            'serie'       => 1,
            'discount'    => 1021,
            'customer_id' => $customer->id,
        ]);

        $order = Order::first();

        $calendarSpy->shouldHaveReceived('store')
            ->with([
                'start'  => '20/10/2016',
                'end'    => '20/10/2016',
                'title'  => "{$order->number} - FAKE CUSTOMER",
            ])
            ->once();
    }

    /** @test */
    public function it_creates_an_order_with_lines()
    {
        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->post("api/orders", [
            'date'        => '20/10/2016',
            'customer_id' => $customer->id,
            'serie'       => 1,
            'lines'       => [
                0 => [
                    'product_id'   => $product->id,
                    'product_name' => 'CUSTOM PRODUCT NAME',
                    'price'        => '12,20',
                    'quantity'     => 2,
                ]
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'date'        => '2016-10-20',
            'customer_id' => $customer->id,
            'serie'       => 1,
        ]);

        $order = Order::first();
        $this->assertDatabaseHas('lines', [
            'lineable_id'   => $order->id,
            'lineable_type' => $order->getMorphClass(),
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);
    }

    /** @test */
    public function serie_is_required_when_creating_an_order()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/orders", [
            'customer_id' => $customer->id,
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function serie_must_be_numeric_when_creating_an_order()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/orders", [
            'customer_id' => $customer->id,
            'serie' => 'NOT A NUMBER',
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function date_is_required_when_creating_an_order()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/orders", [
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function date_must_be_valid_when_creating_an_order()
    {
        $customer = create(Customer::class);
        $response = $this->json('POST', "api/orders", [
            'date' => 'NOT VALID DATE',
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function customer_is_required_when_creating_an_order()
    {
        $response = $this->json('POST', "api/orders", [
            'date' => Carbon::now(),
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }

    /** @test */
    public function all_fields_in_lines_can_be_empty()
    {
        $customer = create(Customer::class);

        $response = $this->json('POST', "api/orders", [
            'date' => '20/10/2016',
            'serie' => 1,
            'customer_id' => $customer->id,
            'lines' => [
                0 => [
                    'product_id'   => '',
                    'product_name' => '',
                    'price'        => '',
                    'quantity'     => '',
                ]
            ]
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_updates_an_order_with_lines()
    {
        $order = create(Order::class);
        create(Line::class, [
            'lineable_id'   => $order->id,
            'lineable_type' => Order::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $customer = create(Customer::class);
        $product = create(Product::class);

        $response = $this->patch("api/orders/{$order->id}", [
            'date'        => '20/10/2016',
            'serie'       => 1,
            'discount' => '10,21',
            'customer_id' => $customer->id,
            'cashed_amount' => 10.27,
            'lines'       => [
                0 => [
                    'product_id'   => $product->id,
                    'product_name' => 'CUSTOM PRODUCT NAME',
                    'price'        => '12,20',
                    'quantity'     => 2,
                ]
            ]
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'date'        => '2016-10-20',
            'serie'       => 1,
            'discount' => 1021,
            'cashed_amount' => 1027,
            'customer_id' => $customer->id,
        ]);

        $order = Order::first();
        $this->assertDatabaseHas('lines', [
            'lineable_id'   => $order->id,
            'lineable_type' => $order->getMorphClass(),
            'product_id'   => $product->id,
            'product_name' => 'CUSTOM PRODUCT NAME',
            'price'        => 1220,
            'quantity'     => 2,
        ]);

        $this->assertCount(1, $order->fresh()->lines);
    }

    /** @test */
    public function date_is_required_when_updating_an_order()
    {
        $order = create(Order::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/orders/{$order->id}", [
            'customer_id' => $customer->id,
            'serie'       => 1,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function serie_is_required_when_updating_an_order()
    {
        $order = create(Order::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/orders/{$order->id}", [
            'customer_id' => $customer->id,
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function serie_must_be_numeric_when_updating_an_order()
    {
        $order = create(Order::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/orders/{$order->id}", [
            'customer_id' => $customer->id,
            'serie' => 'NOT A NUMBER',
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'serie');
    }

    /** @test */
    public function cashed_amount_must_be_numeric_when_updating_an_order()
    {
        $order = create(Order::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/orders/{$order->id}", [
            'customer_id' => $customer->id,
            'serie' => 1,
            'cashed_amount' => 'NOT A NUMBER',
            'date' => Carbon::now(),
        ]);
        $this->assertValidationErrors($response, 'cashed_amount');
    }

    /** @test */
    public function date_must_be_valid_when_updating_an_order()
    {
        $order = create(Order::class);
        $customer = create(Customer::class);
        $response = $this->json('PATCH', "api/orders/{$order->id}", [
            'date' => 'NOT VALID DATE',
            'serie'       => 1,
            'customer_id' => $customer->id,
        ]);
        $this->assertValidationErrors($response, 'date');
    }

    /** @test */
    public function customer_is_required_when_updating_an_order()
    {
        $order = create(Order::class);
        $response = $this->json('PATCH', "api/orders/{$order->id}", [
            'date' => Carbon::now(),
            'serie'       => 1,
        ]);

        $this->assertValidationErrors($response, 'customer_id');
    }

    /** @test */
    public function it_deletes_an_order_with_lines()
    {
        $order = create(Order::class, [
            'date' => '20/10/2016'
        ]);

        create(Line::class, [
            'lineable_id'   => $order->id,
            'lineable_type' => Order::class,
            'price'        => '12,51',
            'quantity'     => 2,
        ]);

        $response = $this->delete("api/orders/{$order->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('orders', [
            'date'        => '2016-10-20',
        ]);

        $this->assertDatabaseMissing('lines', [
            'lineable_id'   => $order->id,
            'lineable_type' => $order->getMorphClass(),
        ]);
    }
}