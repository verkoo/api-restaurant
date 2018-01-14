<?php

use App\Entities\Line;
use App\Entities\MenuOrder;
use App\Entities\Order;
use Verkoo\Common\Entities\User;
use Tests\TestCase;
use App\Entities\Menu;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiMenuOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_adds_a_menu_to_an_order()
    {
        $this->disableExceptionHandling();
        $order = create(Order::class);
        $menu = create(Menu::class);

        $response = $this->post("api/orders/{$order->id}/menus", [
            'menu_id' => $menu->id,
            'name'    => $menu->name
        ]);
        $response->assertStatus(200)
            ->assertJsonFragment(['menu_id' => $menu->id])
            ->assertJsonFragment(['price' => '0,00'])
            ->assertJsonFragment(['name' => $menu->name]);

        $this->assertDatabaseHas('menu_orders', [
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'price' => 0,
            'name' => $menu->name
        ]);
    }

    /** @test */
    public function an_order_returns_the_lines_and_the_menus()
    {
        $order = factory(Order::class)->create();

        $line = factory(Line::class)->create([
            'lineable_id'  => $order->id,
            'lineable_type' => $order->getMorphClass(),
            'product_name'  => 'TEST',
        ]);

        $menu_order = factory(MenuOrder::class)->create([
            'order_id' => $order->id
        ]);

        $response = $this->get("api/orders-tpv/{$order->id}");

        $response->assertJsonFragment([
            'product_name' => 'TEST'
        ]);

        $response->assertJsonFragment($menu_order->toArray());
    }

    /** @test */
    public function it_updates_the_menu_price_from_an_order()
    {
        $order = create(Order::class);
        $menu = create(Menu::class);
        $menuOrder = create(MenuOrder::class, [
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'price' => 1000
        ]);

        $this->assertDatabaseHas('menu_orders', [
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'price' => 1000
        ]);

        $this->put("api/orders/{$order->id}/menus/{$menuOrder->id}", [
            'price' => 2000
        ]);

        $this->assertDatabaseHas('menu_orders', [
            'order_id' => $order->id,
            'menu_id' => $menu->id,
            'price' => 2000
        ]);
    }

    /** @test */
    public function it_deletes_a_menu_from_an_order()
    {
        $order = create(Order::class);
        $menu = create(Menu::class);
        $menuOrder = create(MenuOrder::class, [
            'order_id' => $order->id,
            'menu_id' => $menu->id,
        ]);

        $this->assertDatabaseHas('menu_orders', [
            'order_id' => $order->id,
            'menu_id' => $menu->id
        ]);

        $response = $this->delete("api/orders/{$order->id}/menus/{$menuOrder->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('menu_orders', [
            'order_id' => $order->id,
            'menu_id' => $menu->id
        ]);
    }
}