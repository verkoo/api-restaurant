<?php

use App\Entities\Kitchen;
use Tests\TestCase;
use App\Entities\Dish;
use App\Entities\Menu;
use Verkoo\Common\Entities\User;
use App\Entities\Product;
use App\Entities\DishMenu;
use App\Entities\MenuOrder;
use App\Entities\Combination;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiMenuOrderProductTest extends TestCase
{
    use DatabaseTransactions;

    protected $menu;
    protected $dish;
    protected $product;
    protected $dishMenu;
    protected $menuOrder;

    public function setUp()
    {
        parent::setUp();
        $user = new User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_returns_the_products_the_dishes_and_the_combinations()
    {
        $this->createMenuWithOneDish();
        $this->addProductToTheMenu();
        $this->allowCombination();
        $this->createOrder();

        $this->menuOrder->addProduct([
            'product_id' => $this->product->id,
            'dish_id'    => $this->dish->id
        ]);

        $response = $this->get("api/menu-orders/{$this->menuOrder->id}/products");

        $response->assertStatus(200);

        $response = $response->decodeResponseJson();

        $this->assertEquals($this->product->id,$response['products'][0]['product_id']);
        $this->assertEquals($this->dish->id,$response['products'][0]['pivot']['dish_id']);
        $this->assertEquals($this->dish->id,$response['dishes'][0]['dish_id']);
        $this->assertArrayHasKey('combinations',$response);
    }

    /** @test */
    public function a_product_can_be_added_if_belongs_to_the_menu_and_combination_is_allowed()
    {
        $this->createMenuWithOneDish();
        $this->product = factory(Product::class)->make(['stock' => 10]);
        $this->addProductToTheMenu();
        $this->createOrder();
        $this->allowCombination();

        $kitchen = create(Kitchen::class);

        $response = $this->post("api/menu-orders/{$this->menuOrder->id}/products", [
            'dish_id'       => $this->dish->id,
            'menu_order_id' => $this->menuOrder->id,
            'product_id'    => $this->product->id,
            'kitchen_id'    => $kitchen->id,
        ]);

        $response->assertStatus(201);

        $this->assertEquals(9, $this->product->fresh()->stock);

        $this->assertDatabaseHas('menu_order_product', [
            'dish_id'       => $this->dish->id,
            'menu_order_id' => $this->menuOrder->id,
            'product_id'    => $this->product->id,
            'kitchen_id'    => $kitchen->id,
            'ordered'       => 0,
        ]);
    }

    /** @test */
    public function it_returns_the_combinations_when_adding_a_product_to_the_menu()
    {
        $this->disableExceptionHandling();

        $this->createMenuWithOneDish();
        $this->addProductToTheMenu();
        $this->createOrder();
        $this->allowCombination('20,00');

        $response = $this->post("api/menu-orders/{$this->menuOrder->id}/products", [
            'dish_id'       => $this->dish->id,
            'menu_order_id' => $this->menuOrder->id,
            'product_id'    => $this->product->id
        ]);

        $response->assertStatus(201);

        $response = $response->decodeResponseJson();

        $this->assertEquals('20,00', $response['price']);
        $this->assertCount(1, $response['combinations']);
    }

    /** @test */
    public function a_product_can_not_be_added_if_does_not_belong_to_the_menu()
    {
        $this->createMenuWithOneDish();
        $this->product = factory(Product::class)->create(['stock' => 10]);
        //Product has not been added to the menu
        $this->allowCombination();
        $this->createOrder();

        $response = $this->post("api/menu-orders/{$this->menuOrder->id}/products", [
            'dish_id'       => $this->dish->id,
            'menu_order_id' => $this->menuOrder->id,
            'product_id'    => $this->product->id
        ]);

        $response->assertStatus(422)
            ->assertSee('Product not in menu');

        $this->assertEquals(10, $this->product->fresh()->stock);


        $this->assertDatabaseMissing('menu_order_product', [
            'dish_id'       => $this->dish->id,
            'menu_order_id' => $this->menuOrder->id,
            'product_id'    => $this->product->id
        ]);
    }

    /** @test */
    public function a_product_can_not_be_added_if_combination_is_not_allowed()
    {
        $this->createMenuWithOneDish();
        $this->product = factory(Product::class)->create(['stock' => 10]);
        $this->addProductToTheMenu();
        $this->addProductToTheMenu();
        //Combination has not been allowed
        $this->createOrder();

        $response = $this->post("api/menu-orders/{$this->menuOrder->id}/products", [
            'dish_id'       => $this->dish->id,
            'menu_order_id' => $this->menuOrder->id,
            'product_id'    => $this->product->id
        ]);

        $response->assertStatus(422)
            ->assertSee('Combination not allowed');

        $this->assertEquals(10, $this->product->fresh()->stock);
    }

    /** @test */
    public function a_product_can_not_be_added_twice()
    {
        $this->createMenuWithOneDish();
        $this->product = factory(Product::class)->create(['stock' => 10]);
        $this->addProductToTheMenu();
        $this->addProductToTheMenu();
        $this->allowCombination();
        $this->createOrder();

        $response = $this->post("api/menu-orders/{$this->menuOrder->id}/products", [
            'dish_id'       => $this->dish->id,
            'menu_order_id' => $this->menuOrder->id,
            'product_id'    => $this->product->id
        ]);
        $response->assertStatus(201);
        $this->assertEquals(9, $this->product->fresh()->stock);

        $response = $this->post("api/menu-orders/{$this->menuOrder->id}/products", [
            'dish_id'       => $this->dish->id,
            'menu_order_id' => $this->menuOrder->id,
            'product_id'    => $this->product->id
        ]);

        $response->assertStatus(422)
            ->assertSee('Product is already in the order');

        $this->assertEquals(9, $this->product->fresh()->stock);
    }

    /** @test */
    public function it_gets_all_the_products_from_a_menu_order()
    {
        $this->createMenuWithOneDish();
        $this->addProductToTheMenu();
        $this->allowCombination();
        $this->createOrder();

        $this->menuOrder->addProduct([
            'product_id' => $this->product->id,
            'dish_id'    => $this->dish->id
        ]);

        $response = $this->get("api/menu-orders/{$this->menuOrder->id}/products");
        $response->assertStatus(200);

        $response->assertSEe($this->product->name);
    }

    /** @test */
    public function it_removes_a_product_from_a_menu_order()
    {
        $this->createMenuWithOneDish();
        $this->product = factory(Product::class)->create(['stock' => 10]);
        $this->addProductToTheMenu();
        $this->allowCombination();
        $this->createOrder();

        $this->menuOrder->addProduct([
            'product_id' => $this->product->id,
            'dish_id'    => $this->dish->id
        ]);

        $response = $this->delete("api/menu-orders/{$this->menuOrder->id}/products/{$this->product->id}");
        $response->assertStatus(200);

        $response = $response->decodeResponseJson();

        $this->assertEquals('0,00', $response['price']);
        $this->assertCount(1, $response['combinations']);

        $this->assertEquals(10, $this->product->fresh()->stock);

        $this->assertDatabaseMissing('menu_order_product', [
            'dish_id'       => $this->dish->id,
            'menu_order_id' => $this->menu->id,
            'product_id'    => $this->product->id
        ]);
    }

    protected function allowCombination($price = '10,50')
    {
        $combination = factory(Combination::class)->create([
            'name' => 'One Dish Allowed'
        ]);

        $combination->addDish($this->dish, 1);

        $this->menu->addCombinationWithPrice($combination, $price);
    }

    protected function createMenuWithOneDish()
    {
        $this->menu = factory(Menu::class)->create();
        $this->dish = factory(Dish::class)->create();
        $this->product = factory(Product::class)->make();

        $this->dishMenu = factory(DishMenu::class)->create([
            'menu_id' => $this->menu->id,
            'dish_id' => $this->dish->id,
        ]);
    }

    protected function addProductToTheMenu()
    {
        $this->dishMenu->products()->save($this->product);
    }

    protected function createOrder()
    {
        $this->menuOrder = factory(MenuOrder::class)->create([
            'menu_id' => $this->menu->id
        ]);
    }

}