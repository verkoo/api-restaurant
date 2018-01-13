<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Entities\Dish;
use App\Entities\Combination;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CombinationsTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $this->actingAs($this->adminUser());
    }

    /** @test */
    public function it_can_show_all_the_combinations()
    {
        factory(Combination::class)->create(['name' => 'Combination 1']);

        $response = $this->get('combinations');
        $response->assertSee('Combination 1');
    }

    /** @test */
    public function it_creates_a_new_combination()
    {
        $response = $this->post("combinations", [
            'name' => 'Combination 1',
        ]);

        $this->assertDatabaseHas('combinations', [
            'name'   => 'Combination 1',
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_creating_a_combination()
    {
        $response = $this->json('POST', "combinations");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_updates_a_combination()
    {
        $combination = factory(Combination::class)->create();


        $response = $this->json("PATCH", "combinations/{$combination->id}", [
            'name'   => 'Edit Name',
        ]);

        $this->assertDatabaseHas('combinations', [
            'name'   => 'Edit Name',
            'id'     => $combination->id,
        ]);

        $response->assertStatus(302);
    }

    /** @test */
    public function it_validates_form_when_updating()
    {
        $combination = factory(Combination::class)->create();

        $response = $this->json('PATCH', "combinations/{$combination->id}");
        $this->assertValidationErrors($response, 'name');
    }

    /** @test */
    public function it_deletes_a_combination()
    {
        $combination = factory(Combination::class)->create();

        $this->json('DELETE', "combinations/{$combination->id}");
        $this->assertDatabaseMissing('combinations', [
            'id' => $combination->id
        ]);
    }

    /** @test */
    public function it_adds_a_dish_to_the_combination()
    {
        $combination = factory(Combination::class)->create();
        $dish = factory(Dish::class)->create();

        $this->post("combinations/{$combination->id}/dishes", [
            'dish_id' => $dish->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('combination_dish', [
            'dish_id' => $dish->id,
            'quantity' => 2,
            'combination_id' => $combination->id
        ]);
    }

    /** @test */
    public function it_deletes_a_dish_from_the_combination()
    {
        $combination = factory(Combination::class)->create();
        $dish = factory(Dish::class)->create();
        $combination->dishes()->attach($dish->id, ["quantity" => 2]);

        $this->delete("combinations/{$combination->id}/dishes/{$dish->id}");

        $this->assertDatabaseMissing('combination_dish', [
            'dish_id' => $dish->id,
            'quantity' => 2,
            'combination_id' => $combination->id
        ]);
    }
}
