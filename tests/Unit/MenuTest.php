<?php

namespace Tests\Unit;

use Verkoo\Common\Entities\Tax;
use Tests\TestCase;
use App\Entities\Dish;
use App\Entities\Menu;
use App\Entities\Combination;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MenuTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_menu_has_a_tax()
    {
        $tax = create(Tax::class);
        $order = factory(Menu::class)->create(['tax_id' => $tax->id]);

        $this->assertInstanceOf(Tax::class, $order->tax);
        $this->assertEquals($tax->id, $order->tax->id);
    }

    /** @test */
    public function a_menu_can_add_combinations()
    {
        $dish = factory(Dish::class)->create([
            'name' => 'Primer Plato'
        ]);

        $combination = factory(Combination::class)->create([
            'name' => 'Dos Primeros Platos'
        ]);

        $combination->addDish($dish, 2);

        $menu = factory(Menu::class)->create();

        $menu->addCombinationWithPrice($combination, '12,50');

        $menuCombination = $menu->combinations->first();

        $this->assertEquals($menuCombination->name, 'Dos Primeros Platos');
        $this->assertEquals($menuCombination->price, '12,50');
    }
}
