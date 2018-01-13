<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Entities\Menu;
use App\Entities\Combination;
use Verkoo\Common\Entities\Tax;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MenusTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_menus()
    {
        factory(Menu::class)->create(['name' => 'Menu 1']);

        $response = $this->get('menus');
        $response->assertSee('Menu 1');
    }

    /** @test */
    public function it_creates_a_new_menu()
    {
        $tax = create(Tax::class);

        $response = $this->post("menus", [
            'name' => 'Menu 1',
            'description' => 'Description',
            'tax_id' => $tax->id,
            'active' => 1,
            'bread' => 1,
            'salad' => 1
        ]);

        $this->assertDatabaseHas('menus', [
            'name' => 'Menu 1',
            'description' => 'Description',
            'tax_id' => $tax->id,
            'active' => 1,
            'bread' => 1,
            'salad' => 1
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_a_menu()
    {
        $response = $this->json('POST', "menus");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_updates_a_menu()
    {
        $menu = factory(Menu::class)->create();
        $tax = create(Tax::class);


        $response = $this->json("PATCH", "menus/{$menu->id}", [
            'name' => 'edit item',
            'description' => 'edit description',
            'tax_id' => $tax->id,
        ]);

        $this->assertDatabaseHas('menus', [
            'name' => 'edit item',
            'description' => 'edit description',
            'tax_id' => $tax->id,
            'active' => 0,
            'bread' => 0,
            'salad' => 0,
            'id'     => $menu->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_a_menu()
    {
        $menu = factory(Menu::class)->create();

        $response = $this->json('PATCH', "menus/{$menu->id}");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_menu()
    {
        $menu = factory(Menu::class)->create();

        $this->json('DELETE', "menus/{$menu->id}");
        $this->assertDatabaseMissing('menus',[
            'id' => $menu->id
        ]);
    }

    /** @test */
    public function it_adds_a_combination_when_press_the_button()
    {
        $menu = factory(Menu::class)->create();
        $combination = factory(Combination::class)->create();

        $response = $this->post("menus/{$menu->id}/combinations", [
            'combination_id' => $combination->id,
            'price' => '2,50',
        ]);

        $this->assertDatabaseHas('combination_menu', [
            'combination_id' => $combination->id,
            'price' => 250,
            'menu_id' => $menu->id
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_deletes_a_dish_when_press_the_delete_button()
    {
        $menu = factory(Menu::class)->create();
        $combination = factory(Combination::class)->create();
        $menu->combinations()->attach($combination->id, ["price" => 250]);

        $this->delete("menus/{$menu->id}/combinations/{$combination->id}");

        $this->assertDatabaseMissing('combination_menu', [
            'combination_id' => $combination->id,
            'price' => 250,
            'menu_id' => $menu->id
        ]);
    }
}
