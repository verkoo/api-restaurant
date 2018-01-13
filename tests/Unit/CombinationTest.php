<?php

namespace Tests\Unit;

use App\Entities\Combination;
use App\Entities\Dish;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CombinationTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function a_combination_can_add_dishes()
    {
        $dish = factory(Dish::class)->create([
            'name' => 'Primer Plato'
        ]);

        $combination = factory(Combination::class)->create([
            'name' => 'Dos Primeros Platos'
        ]);

        $combination->addDish($dish, 2);

        $combinationDish = $combination->dishes->first();

        $this->assertEquals($combinationDish->name, 'Primer Plato');
        $this->assertEquals($combinationDish->quantity, 2);
    }
}
