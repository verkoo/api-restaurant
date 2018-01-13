<?php

use Tests\TestCase;
use App\Entities\Line;
use App\Entities\Order;
use App\Entities\Product;
use Verkoo\Common\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiLineTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    public function setUp()
    {
        parent::setUp();
        $this->user = create(User::class);
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function it_gets_the_lines_from_an_order()
    {
        $order = create(Order::class);

        create(Line::class, [
            'lineable_id'   => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'product_name'  => 'TEST'
        ]);

        $response = $this->get("api/orders-tpv/{$order->id}");

        $response->assertJsonFragment([
            'product_name' => 'TEST',
            'hasChildren'  => false,
        ]);
    }
    
    /** @test */
    public function it_adds_lines_to_a_given_order()
    {
        $order = factory(Order::class)->create();
        $product = factory(Product::class)->create();

        $response = $this->post('api/lines', [
            'id'           => $order->id,
            'type'         => 'order',
            'product_id'   => $product->id,
            'product_name' => 'TEST',
            'quantity'     => '1,23',
            'price'        => '2,56',
            'parent'       => 1,
            'discount'     => '0,27',
            'cost'         => $product->cost,
            'vat'          => 21,
            'kitchen_id'   => null,
            'ordered'      => false
        ], ['Accept' => 'application/json']);

        $this->assertDatabaseHas('lines', [
            'product_id'    => $product->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'vat'          => 21,
            'parent'       => 1,
            'quantity'     => 1.23,
            'lineable_id'   => $order->id
        ]);

        $response->assertJsonFragment([
            'product_name' => 'TEST'
        ]);
    }

    /** @test */
    public function it_reduces_the_stock_from_the_product_when_add_a_line()
    {
        $order = factory(Order::class)->create();
        $product = factory(Product::class)->create(['stock' => 50]);

        $this->post('api/lines', [
            'id'           => $order->id,
            'type'         => 'order',
            'product_id'   => $product->id,
            'product_name' => 'test product',
            'quantity'     => '5',
            'price'        => '2,56',
            'discount'     => '0,27',
            'cost'         => $product->cost,
            'vat'          => $product->vat,
            'kitchen_id'   => null,
            'ordered'      => false
        ], ['Accept' => 'application/json']);

        $this->assertDatabaseHas('products', [
            'id'    => $product->id,
            'stock' => 45
        ]);
    }

    /** @test */
    public function it_validates_when_adding_a_line_to_an_order() {

        $response = $this->post('api/lines', [
            'id'         => '',
            'type'       => '',
            'product_id' => '',
            'quantity'   => '',
            'price'      => '',
        ], ['Accept' => 'application/json']);

        $this->assertValidationErrors($response, [
            'id',
            'type',
            'quantity',
            'price',
        ]);
    }

    /** @test */
    public function product_id_is_not_required_when_adding_a_line_to_an_order()
    {
        $order = create(Order::class);

        $response = $this->post('api/lines', [
            'id'         => $order->id,
            'type'       => 'order',
            'quantity'   => 1,
            'price'      => '10',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('lines', [
            'lineable_id' => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'product_id'    => null,
            'quantity' => 1,
            'price'    => 1000,
        ]);

    }

    /** @test */
    public function parent_is_not_required_when_adding_a_line_to_an_order()
    {
        $order = create(Order::class);

        $response = $this->post('api/lines', [
            'id'         => $order->id,
            'type'       => 'order',
            'quantity'   => 5.23,
            'price'      => '10',
        ], ['Accept' => 'application/json']);

        $response->assertStatus(200);

        $this->assertDatabaseHas('lines', [
            'lineable_id' => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'parent'    => null,
            'quantity' => 5.23,
            'price'    => 1000,
        ]);

    }

    /** @test */
    public function it_updates_a_line() {
        $order = factory(Order::class)->create();
        $line = factory(Line::class)->make();
        $order->lines()->save($line);

        $newProduct = factory(Product::class)->create();

        $response = $this->put("api/lines/{$line->id}", [
            'product_id' => $newProduct->id,
            'quantity'   => '2',
            'price'      => '2,56',
            'discount'   => '0,27'
        ], ['Accept' => 'application/json']);

        $this->assertDatabaseHas('lines', [
            'product_id'    => $newProduct->id,
            'quantity' => 2,
            'price'    => 256,
            'discount' => 27
        ]);

        $response->assertJsonFragment([
            'id'      => $line->id
        ]);
    }

    /** @test */
    public function a_product_changes_its_stock_when_a_line_is_updated()
    {
        $product = factory(Product::class)->create(['stock' => 10]);
        $order = factory(Order::class)->create();
        $line = factory(Line::class)->make(['product_id' => $product->id, 'quantity' => 3]);
        $order->lines()->save($line);

        $this->assertDatabaseHas('products', [
            'id'    => $product->id,
            'stock' => 7
        ]);

        $this->put("api/lines/{$line->id}", [
            'quantity'   => '2',
            'price'      => '1'
        ], ['Accept' => 'application/json']);

        $this->assertDatabaseHas('products', [
            'id'    => $product->id,
            'stock' => 8
        ]);
    }

    /** @test */
    public function it_validates_when_updating_a_line() {

        $order = create(Order::class);
        $line = create(Line::class);
        $order->lines()->save($line);

        $response = $this->put("api/lines/{$line->id}", [
            'quantity'   => '',
            'price'      => ''
        ], ['Accept' => 'application/json']);

        $this->assertDatabaseMissing('lines', [
            'product_id' => '',
            'lineable_id' => $order->id
        ]);

        $this->assertValidationErrors($response, [
            'quantity',
            'price',
        ]);
    }

    /** @test */
    public function it_deletes_a_line()
    {
        $order = factory(Order::class)->create();
        $line = factory(Line::class)->make();
        $line = $order->lines()->save($line);
        $this->delete("api/lines/{$line->id}");

        $this->assertDatabaseMissing('lines', [
            'id' => $line->id
        ]);
    }

    /** @test */
    public function a_product_increases_its_stock_when_a_line_is_deleted()
    {
        $product = factory(Product::class)->create(['stock' => 10]);
        $order = factory(Order::class)->create();
        $line = factory(Line::class)->make(['product_id' => $product->id, 'quantity' => 5]);
        $line = $order->lines()->save($line);

        $this->delete("api/lines/{$line->id}");

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 10
        ]);
    }
}
