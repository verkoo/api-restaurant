<?php

use Tests\TestCase;
use App\Entities\Order;
use App\Entities\Table;
use App\Entities\Product;
use Verkoo\Common\Entities\User;
use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Payment;
use Verkoo\Common\Entities\Session;
use Verkoo\Common\Entities\Customer;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiOrderTpvTest extends TestCase
{
    use DatabaseTransactions;
    
    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function it_creates_an_order()
    {
        $this->disableExceptionHandling();
        $customer = factory(Customer::class)->create();
        factory(Options::class)->create(['cash_customer' => $customer->id]);

        $session = factory(Session::class)->create();

        $response = $this->post('api/orders-tpv', [
            'session_id'  => $session->id,
        ], ['Accept' => 'application/json']);
        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'user_id'     => $this->user->id,
            'session_id'  => $session->id
        ]);

        $response->assertJson([
            'id' => Order::first()->id
        ]);
    }

    /** @test */
    public function it_creates_an_order_from_a_table()
    {
        $customer = factory(Customer::class)->create();
        factory(Options::class)->create(['cash_customer' => $customer->id]);
        $table = factory(Table::class)->create();

        $response = $this->post('api/orders-tpv', [
            'table_id'   => $table->id,
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'table_id' => $table->id,
            'user_id'     => $this->user->id
        ]);

        $order = Order::with('table')->first();

        $response->assertJsonFragment([
            'id' => $order->id
        ]);


        $response->assertJson([
            'table' => [
                'id' => $table->id
            ]
        ]);
    }

    /**
     * @test
     */
    public function it_cannot_create_an_order_from_a_table_with_active_order()
    {
        $table = factory(Table::class)->create();
        factory(Order::class)->create(['table_id' => $table->id, 'payment_id' => null]);

        $response = $this->post('api/orders-tpv', [
            'table_id'    => $table->id,
        ]);

        $response->assertSee('Esta mesa ya tiene un pedido abierto');
    }

    /** @test */
    public function it_edits_an_order_from_a_table()
    {
        $table = factory(Table::class)->create();
        $order = factory(Order::class)->create(['table_id' => $table->id]);

        $newTable = factory(Table::class)->create();

        $response = $this->put("api/orders-tpv/{$order->id}", [
            'table_id' => $newTable->id
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);

        $order = Order::with('table')->first();

        $response->assertJsonFragment([
            'id' => $order->id
        ]);

        $response->assertJson([
            'table' => [
                'id' => $newTable->id
            ]
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'table_id' => $newTable->id
        ]);
    }

    /** @test */
    public function it_edits_the_customer_in_an_order()
    {
        $customer = factory(Customer::class)->create();
        $order = factory(Order::class)->create(['table_id' => $customer->id]);

        $newCustomer = factory(Customer::class)->create();

        $response = $this->put("api/orders-tpv/{$order->id}", [
            'customer_id' => $newCustomer->id
        ], ['Accept' => 'application/json']);

        $order = Order::first();
        $response->assertStatus(200)
            ->assertJson([
               'id' => $order->id,
               'customer_id' => $newCustomer->id,
            ]);

        $this->assertDatabaseHas('orders', [
                'id' => $order->id,
                'customer_id' => $newCustomer->id
            ]);
    }

    /** @test */
    public function it_returns_the_active_orders()
    {
        $payment = factory(Payment::class)->create();

        $validOrder = create(Order::class);
        factory(Order::class)->times(2)->create(['payment_id' => $payment->id]);

        $response = $this->get('api/orders-tpv');

        $response->assertStatus(200)
                ->assertJsonFragment([
                    'id' => $validOrder->id
                ]);

        $this->assertCount(1, $response->decodeResponseJson());
    }

    /** @test */
    public function it_deletes_an_order()
    {
        $order = factory(Order::class)->create();

        $response = $this->delete("api/orders-tpv/{$order->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('orders', [
            'id' => $order->id
        ]);
    }

    /** @test */
    public function all_the_products_in_lines_restore_their_stock_when_an_order_is_deleted()
    {
        $product1 = factory(Product::class)->create(['stock' => 4]);
        $product2 = factory(Product::class)->create(['stock' => 3]);

        $order = factory(Order::class)->create();

        $this->post('api/lines', [
            'id'           => $order->id,
            'type'         => 'order',
            'product_id'   => $product1->id,
            'price'        => '2,56',
            'quantity'     => '1'
        ], ['Accept' => 'application/json']);

        $this->post('api/lines', [
            'id'           => $order->id,
            'type'         => 'order',
            'product_id'   => $product2->id,
            'price'        => '2,56',
            'quantity'     => '1'
        ], ['Accept' => 'application/json']);

        $response = $this->delete("api/orders-tpv/{$order->id}");


        $response->assertStatus(200);

        $this->assertDatabaseHas('products', [
            'id' => $product1->id,
            'stock' => 4
        ])
        ->assertDatabaseHas('products', [
            'id' => $product2->id,
            'stock' => 3
        ]);
    }
}
