<?php

namespace Tests\Unit;

use Verkoo\Common\Entities\Options;
use Verkoo\Common\Entities\Tax;
use Tests\TestCase;
use App\Entities\Dish;
use App\Entities\Menu;
use App\Entities\Product;
use App\Entities\DishMenu;
use App\Entities\MenuOrder;
use App\Entities\Combination;
use App\Exceptions\CombinationNotAllowed;
use App\Exceptions\ProductNotInMenuException;
use App\Exceptions\SameProductInMenuException;
use Illuminate\Foundation\Testing\DatabaseTransactions;


class MenuOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected $menu;
    protected $dishMenu;
    protected $dish;
    protected $product;
    protected $menuOrder;

    /** @test */
    public function a_menu_order_has_a_vat()
    {
        $tax = create(Tax::class, ['percentage' => 10]);
        $menu = create(Menu::class, ['tax_id' => $tax->id]);
        $menuOrder = create(MenuOrder::class, ['menu_id' => $menu->id]);

        $this->assertEquals($menuOrder->vat, 10);
    }

    /** @test */
    public function a_menu_order_return_default_vat_if_tax_id_is_null_in_menu()
    {
        $tax = create(Tax::class, ['percentage' => 10]);
        create(Options::class, ['tax_id' => $tax->id]);
        $menu = factory(Menu::class)->create(['tax_id' => null]);
        $menuOrder = create(MenuOrder::class, ['menu_id' => $menu->id]);

        $this->assertEquals($menuOrder->vat, 10);
    }

    /** @test */
    public function it_checks_if_a_product_belongs_to_a_menu()
    {
        $this->createMenuWithOneDishAndOneProduct();
        $this->createOrder();
        $this->assertTrue($this->menuOrder->belongsToMenu($this->product->id, $this->dish->id));
    }

    /** @test */
    public function it_checks_if_there_is_quantity_available_in_some_combination_if_so_it_returns_the_cheaper_one()
    {
        $this->createMenuWithOneDishAndOneProduct();
        $this->createOrder();

        $combination1 = (object) [
            'combination_id' => 1,
            'quantity' => 1,
            'dish_id' => $this->dish->id,
            'price' => '10,50',
        ];

        $combination2 = (object) [
            'combination_id' => 2,
            'quantity' => 2,
            'dish_id' => $this->dish->id,
            'price' => '20,00',
        ];

        $combinations = collect([
           $combination1,
           $combination2,
        ]);

        $this->menuOrder->dishes()->attach($this->dish->id, ['product_id' => $this->product->id]);
        $cheaperCombination = $this->menuOrder->fresh()->availableCombinations($combinations);
        $this->assertEquals('10,50', $cheaperCombination->first()->first()->price);

        $this->menuOrder->dishes()->attach($this->dish->id, ['product_id' => $this->product->id]);
        $cheaperCombination = $this->menuOrder->fresh()->availableCombinations($combinations);
        $this->assertEquals('20,00', $cheaperCombination->first()->first()->price);

        $this->menuOrder->dishes()->attach($this->dish->id, ['product_id' => $this->product->id]);
        try {
            $this->menuOrder->fresh()->availableCombinations($combinations);
        } catch (CombinationNotAllowed $e) {
            return;
        }
        $this->fail('availableCombinations should throw an exception when there is no combination available');
    }

    /** @test */
    public function it_updates_the_price_of_the_menu_order_according_to_the_cheaper_combination()
    {
        $this->createMenuWithOneDishAndOneProduct();
        $this->createOrder();

        //Creates a second dish and adds one product to it
        $cake = factory(Product::class)->create(['name' => 'Cake']);
        $desert = factory(Dish::class)->create(['name' => 'Desert']);
        $dishMenu = factory(DishMenu::class)->create([
            'menu_id' => $this->menu->id,
            'dish_id' => $desert->id,
        ]);
        $dishMenu->products()->save($cake);


        $combination1 = factory(Combination::class)->create([
            'name' => 'One Dish Allowed'
        ]);
        $combination1->addDish($this->dish, 1);

        $combination2 = factory(Combination::class)->create([
            'name' => 'One Dish and a Desert Allowed'
        ]);
        $combination2->addDish($this->dish, 1);
        $combination2->addDish($desert, 1);

        $this->menu->addCombinationWithPrice($combination2, '13');
        $this->menu->addCombinationWithPrice($combination1, '10,50');

        $this->assertEquals('0,00', $this->menuOrder->price);

        $this->menuOrder->addProduct([
            'product_id' => $this->product->id,
            'dish_id' => $this->dish->id,
        ]);

        $this->assertEquals('10,50', $this->menuOrder->fresh()->price);
    }

    /** @test */
    public function it_updates_the_price_of_the_menu_order_when_deleting_a_product()
    {
        $this->createMenuWithOneDishAndOneProduct();
        $this->createOrder();

        //Creates a second dish and adds one product to it
        $cake = factory(Product::class)->create(['name' => 'Cake']);
        $desert = factory(Dish::class)->create(['name' => 'Desert']);
        $dishMenu = factory(DishMenu::class)->create([
            'menu_id' => $this->menu->id,
            'dish_id' => $desert->id,
        ]);
        $dishMenu->products()->save($cake);


        $combination1 = factory(Combination::class)->create([
            'name' => 'One Dish Allowed'
        ]);
        $combination1->addDish($this->dish, 1);

        $combination2 = factory(Combination::class)->create([
            'name' => 'One Dish and a Desert Allowed'
        ]);
        $combination2->addDish($this->dish, 1);
        $combination2->addDish($desert, 1);

        $this->menu->addCombinationWithPrice($combination2, '13');
        $this->menu->addCombinationWithPrice($combination1, '10,50');

        $this->assertEquals('0,00', $this->menuOrder->price);

        $this->menuOrder->addProduct([
            'product_id' => $this->product->id,
            'dish_id' => $this->dish->id,
        ]);
        $this->assertEquals('10,50', $this->menuOrder->fresh()->price);

        $this->menuOrder->addProduct([
            'product_id' => $cake->id,
            'dish_id' => $desert->id,
        ]);

        $this->assertEquals('13,00', $this->menuOrder->fresh()->price);

        $this->menuOrder->deleteProduct($cake->id);
        $this->assertEquals('10,50', $this->menuOrder->fresh()->price);
    }

    /** @test */
    public function it_restores_the_stock_of_the_products_when_deleting_a_menu_order()
    {
        $otherProduct = factory(Product::class)->create(['stock' => 5]);

        $this->createMenuWithOneDish();
        $this->product = factory(Product::class)->create(['stock' => 10]);
        $this->addProductToTheMenu();
        $this->allowCombination();
        $this->createOrder();

        $this->menuOrder->addProduct([
            'product_id' => $this->product->id,
            'dish_id' => $this->dish->id,
        ]);
        $this->assertEquals(9, $this->product->fresh()->stock);

        $this->menuOrder->delete();

        $this->assertEquals(10, $this->product->fresh()->stock);
        $this->assertEquals(5, $otherProduct->fresh()->stock);
    }

    /** @test */
    public function it_throws_an_exception_trying_to_add_a_product_that_does_not_belong_to_the_menu()
    {
        $this->createMenuWithOneDishAndOneProduct();
        $this->createOrder();

        $differentProduct = factory(Product::class)->create();

        try {
            $this->menuOrder->addProduct([
                'product_id' => $differentProduct->id,
                'dish_id' => $this->dish->id,
            ]);
        } catch (ProductNotInMenuException $e) {
            return;
        }

        $this->fail('The product can be added even though does not belong to the menu');
    }

    /** @test */
    public function it_throws_an_exception_trying_to_add_a_dish_that_does_not_match_with_any_combination()
    {
        $this->createMenuWithOneDishAndOneProduct();
        $this->createOrder();
        // Does not allow the combination

        try {
            $this->menuOrder->addProduct([
                'product_id' => $this->product->id,
                'dish_id' => $this->dish->id,
            ]);
        } catch (CombinationNotAllowed $e) {
            $this->assertCount(0, $this->menuOrder->products);
            return;
        }

        $this->fail('The dish can be added even though does not match with any combination');
    }

    /** @test */
    public function it_throws_an_exception_adding_two_different_dishes_with_only_one_allowed()
    {
        $this->createMenuWithOneDishAndOneProduct();
        $this->createOrder();
        $this->allowCombination();

        //First time passes
        $this->menuOrder->addProduct([
            'product_id' => $this->product->id,
            'dish_id' => $this->dish->id,
        ]);

        $product2 = factory(Product::class)->make();
        $this->dishMenu->products()->save($product2);

        try {
            $this->menuOrder->fresh()->addProduct([
                'product_id' => $product2->id,
                'dish_id' => $this->dish->id,
            ]);
        } catch (CombinationNotAllowed $e) {
            return;
        }

        $this->fail('The dish can be added even though the dish quantity is full in the combination');
    }

    /** @test */
    public function it_throws_an_exception_adding_the_same_dish_twice_with_only_one_allowed()
    {
        $this->createMenuWithOneDishAndOneProduct();
        $this->createOrder();
        $this->allowCombination();

        //First time passes
        $this->menuOrder->addProduct([
            'product_id' => $this->product->id,
            'dish_id' => $this->dish->id,
        ]);

        try {
            $this->menuOrder->fresh()->addProduct([
                'product_id' => $this->product->id,
                'dish_id' => $this->dish->id,
            ]);
        } catch (SameProductInMenuException $e) {
            return;
        }

        $this->fail('The dish can be added even though one dish only can be added once');
    }

    protected function allowCombination()
    {
        $combination = factory(Combination::class)->create([
            'name' => 'One Dish Allowed'
        ]);

        $combination->addDish($this->dish, 1);

        $this->menu->addCombinationWithPrice($combination, '10,50');
    }

    protected function createMenuWithOneDishAndOneProduct()
    {
        $this->menu = factory(Menu::class)->create();
        $this->dish = factory(Dish::class)->create();
        $this->product = factory(Product::class)->make();

        $this->dishMenu = factory(DishMenu::class)->create([
            'menu_id' => $this->menu->id,
            'dish_id' => $this->dish->id,
        ]);
        $this->addProductToTheMenu();
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
