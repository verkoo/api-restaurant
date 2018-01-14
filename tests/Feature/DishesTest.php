<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Entities\Dish;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DishesTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_dishes()
    {
        factory(Dish::class)->create(['name' => 'Dish 1']);

        $response = $this->get('dishes');
        $response->assertSee('Dish 1');
    }

    /** @test */
    public function it_creates_a_new_dish()
    {
        $response = $this->post("dishes", [
            'name' => 'Paella',
            'priority' => '1',
            'pass' => '1',
        ]);

        $this->assertDatabaseHas('dishes', [
            'name' => 'Paella',
            'priority' => 1,
            'pass' => 1,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_a_dish()
    {
        $response = $this->json('POST', "dishes");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_updates_a_dish()
    {
        $dish = create(Dish::class, [
            'name' => 'Rice',
            'pass' => 1
        ]);

        $response = $this->json("PATCH", "dishes/{$dish->id}", [
            'name' => 'Paella',
        ]);

        $this->assertDatabaseHas('dishes', [
            'name' => 'Paella',
            'pass' => 0,
            'id'     => $dish->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating_a_dish()
    {
        $dish = factory(Dish::class)->create();

        $response = $this->json('PATCH', "dishes/{$dish->id}");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_dish()
    {
        $dish = factory(Dish::class)->create();

        $this->json('DELETE', "dishes/{$dish->id}");
        $this->assertDatabaseMissing('dishes', [
            'id' => $dish->id
        ]);
    }
}
