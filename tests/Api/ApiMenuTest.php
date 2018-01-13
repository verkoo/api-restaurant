<?php

use App\Entities\Line;
use App\Entities\MenuOrder;
use App\Entities\Order;
use Verkoo\Common\Entities\User;
use App\Http\Transformers\LineTransformer;
use Tests\TestCase;
use App\Entities\Dish;
use App\Entities\Menu;
use App\Entities\Product;
use App\Entities\DishMenu;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiMenuTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_menus()
    {
        $menus = factory(Menu::class,3)->create();

        $response = $this->get("api/menus");
        $response->assertStatus(200);

        $response->assertJson($menus->toArray());
    }

    
    /** @test */
    public function it_adds_a_dish_to_a_menu()
    {
        $menu = factory(Menu::class)->create();
        $dish = factory(Dish::class)->create();
        
        $response = $this->post("api/menus/{$menu->id}/dishes", [
            'dish_id' => $dish->id
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('dish_menu', [
            'dish_id' => $dish->id,
            'menu_id' => $menu->id
        ]);
    }

    /** @test */
    public function it_gets_the_dishes_from_a_menu_with_products()
    {
        $menu = factory(Menu::class)->create();
        $dish = factory(Dish::class)->create();
        $product = factory(Product::class)->make();

        $dishMenu = factory(DishMenu::class)->create([
            'dish_id' => $dish->id,
            'menu_id' => $menu->id,
        ]);

        $dishMenu->products()->save($product);

        $response = $this->get("api/menus/{$menu->id}/dishes");
        $response->assertStatus(200);

        $response->assertJsonFragment($dish->toArray());
        $response->assertJsonFragment($product->toArray());
    }

    /** @test */
    public function it_deletes_dish_from_a_menu()
    {
        $menu = factory(Menu::class)->create();
        $dish = factory(Dish::class)->create();
        $dishMenu = new DishMenu(['dish_id' => $dish->id]);
        $menu->dishes()->save($dishMenu);

        $response = $this->delete("api/menus/{$menu->id}/dishes/{$dishMenu->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('dish_menu', [
            'dish_id' => $dish->id,
            'menu_id' => $menu->id
        ]);
    }

    /** @test */
    public function it_adds_a_product_to_a_dish_from_a_menu()
    {
        $menu = factory(Menu::class)->create();
        $dish = factory(Dish::class)->create();
        $dishMenu = new DishMenu(['dish_id' => $dish->id]);
        $menu->dishes()->save($dishMenu);
        $product = factory(Product::class)->create();

        $dishMenu = $menu->dishes->first();

        $response = $this->post("api/menu-dishes/{$dishMenu->id}/products", [
            'product' => $product->id
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('dish_menu_product', [
            'dish_menu_id' => $dishMenu->id,
            'product_id' => $product->id
        ]);
    }

    /** @test */
    public function it_removes_a_product_from_a_dish_in_a_menu()
    {
        $menu = factory(Menu::class)->create();
        $dish = factory(Dish::class)->create();
        $dishMenu = new DishMenu(['dish_id' => $dish->id]);
        $menu->dishes()->save($dishMenu);
        $product = factory(Product::class)->create();
        $dishMenu->products()->attach([$product->id]);

        $dishMenu = $menu->dishes->first();

        $response = $this->delete("api/menu-dishes/{$dishMenu->id}/products/{$product->id}");
        $response->assertStatus(200);

        $this->assertDatabaseMissing('dish_menu_product', [
            'dish_menu_id' => $dishMenu->id,
            'product_id' => $product->id
        ]);
    }
}