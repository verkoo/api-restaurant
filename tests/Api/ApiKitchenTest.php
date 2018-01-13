<?php

use App\Entities\Dish;
use App\Entities\Line;
use App\Entities\MenuOrder;
use App\Entities\Order;
use App\Entities\Kitchen;
use App\Entities\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiKitchenTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new \Verkoo\Common\Entities\User();
        $this->actingAs($user, 'api');
    }


    /** @test */
    public function it_marks_as_ordered_the_lines_with_kitchen_in_an_order()
    {
        $this->disableExceptionHandling();
        $lines = factory(Line::class)->times(2)->make();
        $kitchen = factory(Kitchen::class)->create();
        $line2 = factory(Line::class)->make(['kitchen_id' => $kitchen->id, 'quantity' => 2]);
        $order = factory(Order::class)->create();
        $order->lines()->saveMany($lines);
        $order->lines()->save($line2);

        $response = $this->put("api/orders/{$order->id}/send-to-kitchen");
        $response->assertStatus(200);

        $line2->quantity = 3;
        $line2->save();
        
        $this->assertDatabaseHas('lines', [
            'id'            => $line2->id,
            'kitchen_id'    => $kitchen->id,
            'lineable_id'   => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'ordered'       => 2
        ]);

        $response = $this->put("api/orders/{$order->id}/send-to-kitchen");
        $response->assertStatus(200);

        $this->assertDatabaseHas('lines', [
            'id'            => $line2->id,
            'kitchen_id'    => $kitchen->id,
            'lineable_id'   => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'ordered'       => 3
        ]);
    }

    /** @test */
    public function it_marks_as_ordered_the_products_with_kitchen_in_an_menu_order()
    {
        $order = create(Order::class);
        $menuOrder = create(MenuOrder::class, ['order_id' => $order->id]);

        $product = create(Product::class);
        $dish = create(Dish::class);

        $menuOrder->products()->sync([
            $product->id => [
                'dish_id' => $dish->id,
                'kitchen_id' => 1,
            ]
        ], false);

        $response = $this->put("api/orders/{$order->id}/send-to-kitchen");
        $response->assertStatus(200);

        $this->assertDatabaseHas('menu_order_product', [
            'product_id'    => $product->id,
            'kitchen_id'    => 1,
            'dish_id'       => $dish->id,
            'ordered'       => 1
        ]);
    }

    /** @test */
    public function it_marks_as_ordered_not_served_lines()
    {

        $kitchen = factory(Kitchen::class)->create();
        $line2 = factory(Line::class)->make(['kitchen_id' => $kitchen->id, 'quantity' => 2, 'ordered' => 1]);
        $order = factory(Order::class)->create();
        $order->lines()->save($line2);

        $response = $this->put("api/orders/{$order->id}/send-to-kitchen?served");
        $response->assertStatus(200);

        $this->assertDatabaseHas('lines', [
            'id'            => $line2->id,
            'kitchen_id'    => $kitchen->id,
            'lineable_id'   => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'ordered'       => 2
        ]);
    }

    /** @test */
    public function it_discard_not_served_lines()
    {
        $this->disableExceptionHandling();
        $kitchen = factory(Kitchen::class)->create();
        $line2 = factory(Line::class)->make(['kitchen_id' => $kitchen->id, 'quantity' => 2, 'ordered' => 1]);
        $order = factory(Order::class)->create();
        $order->lines()->save($line2);

        $response = $this->put("api/orders/{$order->id}/send-to-kitchen?discard");
        $response->assertStatus(200);

        $this->assertDatabaseHas('lines', [
            'id'            => $line2->id,
            'kitchen_id'    => $kitchen->id,
            'lineable_id'   => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class,
            'quantity'      => 1
        ]);
    }

    /** @test */
    public function it_discard_not_served_lines_when_quantity_is_one()
    {

        $kitchen = factory(Kitchen::class)->create();
        $line1 = factory(Line::class)->make();
        $line2 = factory(Line::class)->make(['kitchen_id' => $kitchen->id, 'quantity' => 1, 'ordered' => 0]);
        $order = factory(Order::class)->create();
        $order->lines()->save($line1);
        $order->lines()->save($line2);

        $response = $this->put("api/orders/{$order->id}/send-to-kitchen?discard");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('lines', [
            'id'            => $line2->id,
            'lineable_id'   => $order->id,
            'lineable_type' => get_class($order)
        ]);

        $this->assertDatabaseHas('lines', [
            'id'            => $line1->id,
            'lineable_id'   => $order->id,
            'lineable_type' => \Verkoo\Common\Entities\Order::class
        ]);
    }

    /** @test */
    public function it_fires_an_event_when_send_to_kitchen()
    {
        $this->expectsEvents(\App\Events\OrderSentToKitchen::class);
        $order = factory(Order::class)->create();

        $this->put("api/orders/{$order->id}/send-to-kitchen");
    }

    /** @test */
    public function it_handles_a_listener_when_the_event_is_fired()
    {
        $listener = Mockery::spy(\App\Listeners\PrintKitchenTicket::class);
        app()->instance(\App\Listeners\PrintKitchenTicket::class, $listener);

        $order = factory(Order::class)->create();
        $this->put("api/orders/{$order->id}/send-to-kitchen");

        $listener->shouldHaveReceived('handle')->once();
    }
}