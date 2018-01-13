<?php

use App\Entities\Table;
use App\Entities\Zone;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiZoneTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp()
    {
        parent::setUp();
        $user = new \Verkoo\Common\Entities\User();
        $this->actingAs($user, 'api');
    }

    /** @test */
    public function it_gets_all_the_zones()
    {
        $zones  = factory(Zone::class)->times(4)->create();

        $response = $this->get('api/zones');
        $response->assertStatus(200)
            ->assertJson($zones->toArray());
    }
}
