<?php

use Tests\TestCase;
use App\Entities\Dish;
use Verkoo\Common\Entities\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiDishTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_dishes()
    {
        $dish1 = factory(Dish::class)->create();
        $dish2 = factory(Dish::class)->create();

        $response = $this->get("api/dishes");

        $response->assertStatus(200);

        $response->assertJsonFragment(
                $dish1->toArray()
        );
        $response->assertJsonFragment(
                $dish2->toArray()
        );
    }
}